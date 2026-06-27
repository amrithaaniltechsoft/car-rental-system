<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
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
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'supplier_code']);
        $billNumbers = Bill::orderBy('bill_number')->pluck('bill_number');
        $invoiceNumbers = Invoice::orderBy('invoice_number')->pluck('invoice_number');
        $customers = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('adminlte.bills.index', compact('invoices', 'suppliers', 'billNumbers', 'invoiceNumbers', 'customers'));
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
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill): JsonResponse
    {
        $bill->load(['invoice.customer', 'invoice.booking.vehicle']);

        $customerName = $bill->invoice->customer->customer_type === 'company'
            ? $bill->invoice->customer->company_name
            : $bill->invoice->customer->name;

        $vehicleName = $bill->invoice->booking?->vehicle?->name ?? '';

        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'invoice_number' => $bill->invoice->invoice_number,
                'customer_name' => $customerName,
                'vehicle_name' => $vehicleName,
                'bill_date' => $bill->bill_date->format('Y-m-d'),
                'status' => $bill->status,
                'invoice_amount' => $bill->invoice->amount,
                'amount_usd' => $bill->amount_usd,
                'billing_details' => $bill->billing_details ?? [],
            ],
        ]);
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

        // Calculate net profit
        $totalPayable = 0;
        $vatAmt = 0;
        if (! empty($request->billing_details)) {
            foreach ($request->billing_details as $detail) {
                $totalPayable += (float) ($detail['total_payable'] ?? 0);
                $vatAmt += (float) ($detail['vat_amount'] ?? 0);
            }
        }
        $invAmt = (float) ($request->amount_omr ?: $request->amount);
        $totalPayable = round($totalPayable, 3);
        $netProfit = round($invAmt - $totalPayable, 3);
        $validated['net_profit'] = $netProfit;

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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'bill_date' => 'required|date',
            'supplier_id' => 'nullable|array',
            'supplier_id.*' => 'nullable|exists:suppliers,id',
            'purpose' => 'nullable|array',
            'purpose.*' => 'nullable|string|max:255',
        ]);

        $validated['bill_date'] = $request->bill_date;

        // Build billing details from array inputs
        $billingDetails = [];
        if ($request->has('supplier_id')) {
            foreach ($request->supplier_id as $i => $supplierId) {
                if (! empty($supplierId)) {
                    $billingDetails[] = [
                        'supplier_id' => $supplierId,
                        'purpose' => $request->purpose[$i] ?? '',
                        'vat' => $request->vat[$i] ?? 0,
                        'vat_amount' => str_replace(',', '', $request->vat_amount[$i] ?? 0),
                        'total_payable' => str_replace(',', '', $request->total_payable[$i] ?? 0),
                    ];
                }
            }
        }

        // Calculate net profit
        $totalPayable = 0;
        $vatAmt = 0;
        foreach ($billingDetails as $detail) {
            $totalPayable += (float) ($detail['total_payable'] ?? 0);
            $vatAmt += (float) ($detail['vat_amount'] ?? 0);
        }
        $invAmt = (float) ($bill->amount_omr ?: $bill->amount);
        $totalPayable = round($totalPayable, 3);
        $netProfit = round($invAmt - $totalPayable, 3);

        $bill->update([
            'bill_date' => $validated['bill_date'],
            'billing_details' => $billingDetails,
            'net_profit' => $netProfit,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill updated successfully.',
            ]);
        }

        return redirect()->route('bills.index')->with('success', 'Bill updated successfully.');
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

        $filterBillId = $request->input('filter_bill_id');
        $filterInvoiceId = $request->input('filter_invoice_id');
        $filterCustomer = $request->input('filter_customer');

        $tableColumnsMap = [
            0 => 'id',
            1 => 'bill_number',
            2 => 'invoice_id',
            3 => 'id',
            4 => 'amount',
            5 => 'id',
            6 => 'id',
            7 => 'id',
            8 => 'id',
        ];
        $orderColumn = $tableColumnsMap[$orderColumnIndex] ?? 'id';

        $query = Bill::with(['invoice.customer']);

        if (! empty($filterBillId)) {
            $query->where('bill_number', $filterBillId);
        }
        if (! empty($filterInvoiceId)) {
            $query->whereHas('invoice', function ($q) use ($filterInvoiceId) {
                $q->where('invoice_number', $filterInvoiceId);
            });
        }
        if (! empty($filterCustomer)) {
            $query->whereHas('invoice.customer', function ($q) use ($filterCustomer) {
                $q->where('id', $filterCustomer);
            });
        }

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
            $editUrl = route('bills.edit', $bill->id);
            $deleteUrl = route('bills.destroy', $bill->id);

            // Compute totals
            $totalPayable = 0;
            $vatAmt = 0;
            if (! empty($bill->billing_details)) {
                foreach ($bill->billing_details as $detail) {
                    $totalPayable += (float) ($detail['total_payable'] ?? 0);
                    $vatAmt += (float) ($detail['vat_amount'] ?? 0);
                }
            }
            $invAmt = round((float) $bill->invoice->amount, 3);
            $totalPayable = round($totalPayable, 3);
            $netProfit = round($invAmt - $totalPayable, 3);

            $isPaid = $bill->status === 'paid';
            $editClass = $isPaid ? 'btn-default' : 'btn-warning';
            $deleteClass = $isPaid ? 'btn-default' : 'btn-danger';
            $disabledAttr = $isPaid ? 'disabled' : '';

            $data[] = [
                'id' => $rowNum++,
                'bill_number' => $bill->bill_number,
                'invoice' => $bill->invoice->invoice_number,
                'customer' => $customerName,
                'amount' => number_format((float) $bill->invoice->amount, 3),
                'total' => number_format($totalPayable, 3),
                'vat_amount' => number_format($vatAmt, 3),
                'net_profit' => number_format($netProfit, 3),
                'bill_date' => $bill->bill_date->format('d/m/Y'),
                'status' => $this->getStatusBadge($bill->status),
                'actions' => '
                    <button type="button" class="btn btn-info btn-sm view-bill-btn" data-url="'.$showUrl.'" data-toggle="tooltip" title="View Bill">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn '.$editClass.' btn-sm edit-bill-btn" data-url="'.$editUrl.'" data-toggle="tooltip" title="Edit Bill" '.$disabledAttr.'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn '.$deleteClass.' btn-sm delete-bill-btn" data-url="'.$deleteUrl.'" data-toggle="tooltip" title="Delete Bill" '.$disabledAttr.'>
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
        $invoice = $bill->invoice;
        if ($invoice) {
            $invoice->update(['status' => 'pending']);
        }

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
