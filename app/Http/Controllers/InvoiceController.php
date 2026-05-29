<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('adminlte.invoices.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'invoice_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue',
            'description' => 'nullable|string',
        ]);

        if (! empty($validated['booking_id'])) {
            $booking = Booking::findOrFail($validated['booking_id']);

            if ($booking->customer_id != $validated['customer_id']) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The selected booking does not belong to this customer.',
                        'errors' => ['booking_id' => ['The selected booking does not belong to this customer.']],
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['booking_id' => 'The selected booking does not belong to this customer.']);
            }

            if ($booking->invoice()->exists()) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This booking already has an invoice.',
                        'errors' => ['booking_id' => ['This booking already has an invoice.']],
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['booking_id' => 'This booking already has an invoice.']);
            }
        }

        $validated['invoice_number'] = $this->generateInvoiceNumber();

        Invoice::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Get invoices data for DataTables AJAX.
     */
    public function getData(Request $request): JsonResponse
    {
        $columns = [
            'id',
            'invoice_number',
            'customer_id',
            'booking_id',
            'amount',
            'invoice_date',
            'status',
        ];

        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $tableColumnsMap = [
            0 => 'id',
            1 => 'invoice_number',
            2 => 'customer_id',
            3 => 'booking_id',
            4 => 'amount',
            5 => 'invoice_date',
            6 => 'status',
        ];
        $orderColumn = $tableColumnsMap[$orderColumnIndex] ?? 'id';

        $query = Invoice::with(['customer', 'booking.vehicle']);

        if (! empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('invoice_number', 'like', "%{$searchValue}%")
                    ->orWhere('amount', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                        $customerQuery->where('name', 'like', "%{$searchValue}%")
                            ->orWhere('company_name', 'like', "%{$searchValue}%");
                    });
            });
        }

        $recordsTotal = Invoice::count();
        $recordsFiltered = (clone $query)->count();

        $invoices = $query
            ->orderBy($orderColumn, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];
        $rowNum = $start + 1;

        foreach ($invoices as $invoice) {
            $customerName = $invoice->customer->customer_type === 'company'
                ? $invoice->customer->company_name
                : $invoice->customer->name;

            $bookingLabel = '—';
            if ($invoice->booking) {
                $bookingLabel = '#'.$invoice->booking->id.' — '.$invoice->booking->vehicle->name;
            }

            $data[] = [
                'id' => $rowNum++,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $customerName,
                'booking' => $bookingLabel,
                'amount' => number_format((float) $invoice->amount, 2).' OMR',
                'invoice_date' => $invoice->invoice_date->format('d/m/Y'),
                'status' => $this->getStatusBadge($invoice->status),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $lastNumber = Invoice::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = 1;
        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, -4) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function getStatusBadge(string $status): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'paid' => '<span class="badge badge-success">Paid</span>',
            'overdue' => '<span class="badge badge-danger">Overdue</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">'.ucfirst($status).'</span>';
    }

    private function customersForSelect(): Collection
    {
        return Customer::query()
            ->select('id', 'customer_type', 'name', 'company_name')
            ->orderBy('name')
            ->get();
    }

    private function bookingsForSelect(): Collection
    {
        return Booking::query()
            ->with(['vehicle:id,name,registration_number'])
            ->whereDoesntHave('invoice')
            ->select('id', 'customer_id', 'vehicle_id', 'from_date', 'to_date', 'total_amount')
            ->orderByDesc('id')
            ->get();
    }
}
