<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $invoices = $this->invoicesForSelect();

        return view('adminlte.bills.index', compact('invoices'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill): View
    {
        $bill->load(['invoice.customer', 'invoice.booking.vehicle']);

        return view('adminlte.bills.show_modal', compact('bill'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'bill_date' => 'required|date',
            'status' => 'required|in:unpaid,paid,overdue',
            'bill_number' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        if ($invoice->bill()->exists()) {
            $message = 'This invoice already has a bill.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['invoice_id' => [$message]],
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['invoice_id' => $message]);
        }

        // Use provided bill number if available, otherwise generate it
        if ($request->filled('bill_number')) {
            $validated['bill_number'] = $request->bill_number;
        } else {
            $validated['bill_number'] = $this->generateBillNumber();
        }

        Bill::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully.',
            ]);
        }

        return redirect()->route('bills.index')
            ->with('success', 'Bill created successfully.');
    }

    /**
     * Get bills data for DataTables AJAX.
     */
    public function getData(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $tableColumnsMap = [
            0 => 'id',
            1 => 'bill_number',
            2 => 'invoice_id',
            3 => 'customer',
            4 => 'amount',
            5 => 'bill_date',
            6 => 'status',
            7 => 'actions',
        ];
        $orderColumn = $tableColumnsMap[$orderColumnIndex] ?? 'id';

        $query = Bill::with(['invoice.customer']);

        if (! empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('bill_number', 'like', "%{$searchValue}%")
                    ->orWhere('amount', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('invoice', function ($invoiceQuery) use ($searchValue) {
                        $invoiceQuery->where('invoice_number', 'like', "%{$searchValue}%")
                            ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                                $customerQuery->where('name', 'like', "%{$searchValue}%")
                                    ->orWhere('company_name', 'like', "%{$searchValue}%");
                            });
                    });
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

        foreach ($bills as $bill) {
            $customerName = $bill->invoice->customer->customer_type === 'company'
                ? $bill->invoice->customer->company_name
                : $bill->invoice->customer->name;

            $showUrl = route('bills.show', $bill->id);
            $deleteUrl = route('bills.destroy', $bill->id);

            $data[] = [
                'id' => $rowNum++,
                'bill_number' => $bill->bill_number,
                'invoice' => $bill->invoice->invoice_number,
                'customer' => $customerName,
                'amount' => number_format((float) $bill->amount, 2).' OMR',
                'bill_date' => $bill->bill_date->format('d/m/Y'),
                'status' => $this->getStatusBadge($bill->status),
                'actions' => '
                    <button type="button" class="btn btn-info btn-sm view-bill-btn" data-url="'.$showUrl.'" data-toggle="tooltip" title="View Bill">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm delete-bill-btn" data-url="'.$deleteUrl.'" data-toggle="tooltip" title="Delete Bill">
                        <i class="fas fa-trash"></i>
                    </button>
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

    private function generateBillNumber(): string
    {
        $prefix = 'BL'.now()->format('Ymd');
        $lastBill = Bill::query()
            ->where('bill_number', 'like', $prefix.'%')
            ->orderByDesc('bill_number')
            ->first();

        $sequence = 1;
        if ($lastBill) {
            $sequence = (int) substr($lastBill->bill_number, -3) + 1;
        }

        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function getStatusBadge(string $status): string
    {
        $badges = [
            'unpaid' => '<span class="badge badge-warning">Unpaid</span>',
            'paid' => '<span class="badge badge-success">Paid</span>',
            'overdue' => '<span class="badge badge-danger">Overdue</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">'.ucfirst($status).'</span>';
    }

    private function invoicesForSelect(): Collection
    {
        return Invoice::query()
            ->with('customer:id,customer_type,name,company_name')
            ->whereDoesntHave('bill')
            ->select('id', 'invoice_number', 'customer_id', 'amount', 'invoice_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill): JsonResponse|RedirectResponse
    {
        $bill->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully.',
            ]);
        }

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully.');
    }

    public function getNextBillNumber(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'bill_number' => $this->generateBillNumber(),
        ]);
    }
}
