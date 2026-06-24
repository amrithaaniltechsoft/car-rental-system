<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $billNumbers = Bill::orderBy('bill_number')->pluck('bill_number');
        $vehicles = Vehicle::orderBy('name')->get(['id', 'name', 'number_plate']);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('adminlte.reports.index', compact('billNumbers', 'vehicles', 'customers'));
    }

    public function show(Bill $bill): View
    {
        $bill->load(['invoice.customer', 'invoice.booking.vehicle']);

        $supplierIds = collect($bill->billing_details)->pluck('supplier_id')->filter()->unique()->toArray();
        $suppliers = Supplier::whereIn('id', $supplierIds)->get()->keyBy('id');

        $invAmt = round((float) ($bill->invoice->amount * ($bill->exchange_rate ?? 0.3845)), 3);

        return view('adminlte.reports.show', compact('bill', 'suppliers', 'invAmt'));
    }

    public function getData(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $filterBillId = $request->input('filter_bill_id');
        $filterVehicle = $request->input('filter_vehicle');
        $filterCustomer = $request->input('filter_customer');

        $tableColumnsMap = [
            0 => 'id',
            1 => 'bill_date',
            2 => 'bill_number',
            3 => 'id',
            4 => 'id',
            5 => 'id',
            6 => 'id',
            7 => 'id',
        ];
        $orderColumn = $tableColumnsMap[$orderColumnIndex] ?? 'bill_date';

        $query = Bill::with(['invoice.customer', 'invoice.booking.vehicle']);

        if (! empty($filterBillId)) {
            $query->where('bill_number', $filterBillId);
        }
        if (! empty($filterVehicle)) {
            $query->whereHas('invoice.booking', function ($q) use ($filterVehicle) {
                $q->where('vehicle_id', $filterVehicle);
            });
        }
        if (! empty($filterCustomer)) {
            $query->whereHas('invoice.customer', function ($q) use ($filterCustomer) {
                $q->where('id', $filterCustomer);
            });
        }

        $recordsTotal = Bill::count();
        $recordsFiltered = (clone $query)->count();

        $bills = $query
            ->orderBy($orderColumn, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];
        $rowNum = $start + 1;

        // Collect all unique supplier IDs from billing_details
        $allSupplierIds = collect($bills)->flatMap(function ($bill) {
            return collect($bill->billing_details)->pluck('supplier_id');
        })->unique()->filter()->values()->toArray();

        $suppliers = Supplier::whereIn('id', $allSupplierIds)->get()->keyBy('id');

        foreach ($bills as $bill) {
            $customerName = $bill->invoice->customer->customer_type === 'company'
                ? $bill->invoice->customer->company_name
                : $bill->invoice->customer->name;

            $vehicleName = optional($bill->invoice->booking->vehicle)->name ?? '—';

            $viewUrl = route('reports.show', $bill->id);

            $supplierNames = [];
            $totalPayable = 0;
            if (! empty($bill->billing_details)) {
                foreach ($bill->billing_details as $detail) {
                    $totalPayable += (float) ($detail['total_payable'] ?? 0);
                    $supplier = $suppliers->get($detail['supplier_id']);
                    if ($supplier) {
                        $supplierNames[] = $supplier->name;
                    }
                }
            }

            $data[] = [
                'id' => $rowNum++,
                'bill_date' => $bill->bill_date->format('d/m/Y'),
                'suppliers' => implode(', ', array_unique($supplierNames)),
                'vehicle' => $vehicleName,
                'bill_number' => $bill->bill_number,
                'customer' => $customerName,
                'total_payable' => number_format(round($totalPayable, 3), 3),
                'actions' => '
                    <a href="'.$viewUrl.'" target="_blank" class="btn btn-info btn-sm" data-toggle="tooltip" title="View Report">
                        <i class="fas fa-eye"></i>
                    </a>
                ',
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
