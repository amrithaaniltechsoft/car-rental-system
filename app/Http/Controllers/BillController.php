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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'status' => 'required|in:unpaid,paid,overdue',
            'notes' => 'nullable|string',
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

        $validated['bill_number'] = $this->generateBillNumber();
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
            3 => 'amount',
            4 => 'bill_date',
            5 => 'due_date',
            6 => 'status',
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

            $data[] = [
                'id' => $rowNum++,
                'bill_number' => $bill->bill_number,
                'invoice' => $bill->invoice->invoice_number,
                'customer' => $customerName,
                'amount' => number_format((float) $bill->amount, 2).' OMR',
                'bill_date' => $bill->bill_date->format('d/m/Y'),
                'due_date' => $bill->due_date?->format('d/m/Y') ?? '—',
                'status' => $this->getStatusBadge($bill->status),
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
        $prefix = 'BILL-'.now()->format('Ymd').'-';
        $lastNumber = Bill::query()
            ->where('bill_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('bill_number');

        $sequence = 1;
        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, -4) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
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
}
