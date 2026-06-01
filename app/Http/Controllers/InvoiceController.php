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
     * Display the specified resource.
     */
    public function show(Invoice $invoice): View
    {
        if (request()->ajax()) {
            return view('adminlte.invoices.show_modal', compact('invoice'));
        }
        return view('adminlte.invoices.show', compact('invoice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'total' => 'required|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'vat' => 'required|numeric|min:0',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
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

        // Calculate subtotal and vat_amount from total and vat percentage
        $validated['vat_amount'] = ($validated['total'] * $validated['vat']) / 100;
        $validated['subtotal'] = $validated['total'] - $validated['vat_amount'];
        $validated['amount'] = $validated['total'];

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
            'due_date',
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
            5 => 'amount',
            6 => 'amount',
            7 => 'status',
            8 => 'id',
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
            $bookingFromDate = '—';
            $bookingToDate = '—';
            if ($invoice->booking) {
                $bookingLabel = '#'.$invoice->booking->id.' — '.$invoice->booking->vehicle->name;
                $bookingFromDate = $invoice->booking->from_date->format('d/m/Y');
                $bookingToDate = $invoice->booking->to_date->format('d/m/Y');
            }

            $data[] = [
                'id' => $rowNum++,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $customerName,
                'booking' => $bookingLabel,
                'booking_from_date' => $bookingFromDate,
                'booking_to_date' => $bookingToDate,
                'amount' => number_format((float) $invoice->amount, 2),
                'status' => $this->getStatusBadge($invoice->status),
                'actions' => $this->getActionButtons($invoice),
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

    public function getBookingsByCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $bookings = Booking::query()
            ->with(['vehicle:id,name,registration_number'])
            ->where('customer_id', $request->customer_id)
            ->whereDoesntHave('invoice')
            ->select('id', 'customer_id', 'vehicle_id', 'from_date', 'to_date', 'total_amount')
            ->orderByDesc('id')
            ->get();

        $bookingsData = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'label' => '#'.$booking->id.' — '.$booking->vehicle->name.' ('.$booking->vehicle->registration_number.') — '.$booking->from_date->format('d/m/Y').' to '.$booking->to_date->format('d/m/Y'),
                'amount' => $booking->total_amount,
                'from_date' => $booking->from_date->format('Y-m-d'),
            ];
        });

        return response()->json([
            'success' => true,
            'bookings' => $bookingsData,
        ]);
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons(Invoice $invoice): string
    {
        $buttons = '
            <button type="button" class="btn btn-info btn-sm view-invoice-btn" data-id="'.$invoice->id.'" data-url="'.route('invoices.show', $invoice).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm edit-invoice-btn" data-id="'.$invoice->id.'" data-url="'.route('invoices.edit', $invoice).'">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm delete-invoice-btn" data-id="'.$invoice->id.'" data-url="'.route('invoices.destroy', $invoice).'">
                <i class="fas fa-trash"></i>
            </button>
        ';

        return $buttons;
    }
}
