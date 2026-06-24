<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\Vehicle;
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
        $customers = Customer::select('id', 'name', 'company_name', 'customer_type')->orderBy('name')->get();
        $vehicles = Vehicle::select('id', 'name', 'number_plate')->orderBy('name')->get();
        $invoiceNumbers = Invoice::distinct()->orderBy('invoice_number')->pluck('invoice_number')->filter()->values();
        $suppliers = Supplier::select('id', 'supplier_code', 'name')->orderBy('name')->get();

        return view('adminlte.invoices.index', compact('customers', 'vehicles', 'invoiceNumbers', 'suppliers'));
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

        // Apply custom filters
        if ($request->filled('filter_invoice')) {
            $query->where('invoice_number', $request->input('filter_invoice'));
        }
        if ($request->filled('filter_customer')) {
            $query->where('customer_id', $request->input('filter_customer'));
        }
        if ($request->filled('filter_vehicle')) {
            $query->whereHas('booking.vehicle', function ($q) use ($request) {
                $q->where('id', $request->input('filter_vehicle'));
            });
        }
        if ($request->filled('filter_from_date')) {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->whereDate('from_date', $request->input('filter_from_date'));
            });
        }
        if ($request->filled('filter_to_date')) {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->whereDate('to_date', $request->input('filter_to_date'));
            });
        }

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

            $vehicleLabel = '—';
            $bookingFromDate = '—';
            $bookingToDate = '—';
            if ($invoice->booking && $invoice->booking->vehicle) {
                $vehicleLabel = $invoice->booking->vehicle->name.' ('.$invoice->booking->vehicle->number_plate.')';
                $bookingFromDate = $invoice->booking->from_date->format('d/m/Y');
                $bookingToDate = $invoice->booking->to_date->format('d/m/Y');
            }

            $statusBadge = $this->getStatusBadge($invoice->status);
            $billIndicator = '';
            if (! $invoice->bill()->exists()) {
                if ($invoice->status === 'billed') {
                    $invoice->update(['status' => 'pending']);
                    $statusBadge = $this->getStatusBadge('pending');
                }
                $billIndicator = '
                    <button type="button" class="btn btn-success btn-sm create-bill-btn" data-toggle="tooltip" title="Add Bill" data-id="'.$invoice->id.'" data-invoice-number="'.$invoice->invoice_number.'" data-amount="'.$invoice->amount.'" data-subtotal="'.$invoice->subtotal.'" data-vat="'.$invoice->vat.'" data-vat-amount="'.$invoice->vat_amount.'" data-customer="'.($invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name).'">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </button>
                ';
            }

            $data[] = [
                'id' => $rowNum++,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $customerName,
                'vehicle' => $vehicleLabel,
                'booking_from_date' => $bookingFromDate,
                'booking_to_date' => $bookingToDate,
                'amount' => number_format((float) $invoice->amount * 0.3845, 2).' OMR',
                'status' => $statusBadge.' '.$billIndicator,
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
            'pending' => '',
            'paid' => '<span class="badge badge-success">Paid</span>',
            'overdue' => '<span class="badge badge-danger">Overdue</span>',
            'billed' => '<span class="badge badge-info">Invoice Billed</span>',
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
            ->with(['vehicle:id,name,number_plate'])
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
            ->with(['vehicle:id,name,number_plate'])
            ->where('customer_id', $request->customer_id)
            ->whereDoesntHave('invoice')
            ->select('id', 'customer_id', 'vehicle_id', 'from_date', 'to_date', 'total_amount')
            ->orderByDesc('id')
            ->get();

        $bookingsData = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'label' => '#'.$booking->id.' — '.$booking->vehicle->name.' ('.$booking->vehicle->number_plate.') — '.$booking->from_date->format('d/m/Y').' to '.$booking->to_date->format('d/m/Y'),
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
     * Create a bill for invoice
     */
    public function createBill(Request $request, Invoice $invoice): JsonResponse|RedirectResponse
    {
        $request->validate([
            'amount_usd' => 'required|numeric|min:0',
            'bill_date' => 'required|date',
            'bill_number' => 'nullable|string',
            'supplier_id' => 'nullable|array',
            'supplier_id.*' => 'nullable|exists:suppliers,id',
            'purpose' => 'nullable|array',
            'purpose.*' => 'nullable|string|max:255',
            'vat' => 'nullable|array',
            'vat.*' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|array',
            'vat_amount.*' => 'nullable|numeric|min:0',
            'total_payable' => 'nullable|array',
            'total_payable.*' => 'nullable|numeric|min:0',
        ]);

        $exchangeRate = 0.3845; // Fixed exchange rate

        if ($invoice->bill()->exists()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice already has a bill.',
                ], 422);
            }

            return redirect()->back()->with('error', 'This invoice already has a bill.');
        }

        // Use provided bill number if available, otherwise generate it
        if ($request->filled('bill_number')) {
            $billNumber = $request->bill_number;
        } else {
            // Generate bill number using the same format as booking ID
            $prefix = 'BL'.now()->format('Ymd');
            $lastBill = Bill::query()
                ->where('bill_number', 'like', $prefix.'%')
                ->orderByDesc('bill_number')
                ->first();

            $sequence = 1;
            if ($lastBill) {
                $sequence = (int) substr($lastBill->bill_number, -3) + 1;
            }

            $billNumber = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        }

        // Build billing details from array inputs
        $billingDetails = [];
        $totalPayable = 0;
        $vatAmt = 0;
        if ($request->has('supplier_id')) {
            foreach ($request->supplier_id as $i => $supplierId) {
                if (! empty($supplierId)) {
                    $tp = str_replace(',', '', $request->total_payable[$i] ?? 0);
                    $va = str_replace(',', '', $request->vat_amount[$i] ?? 0);
                    $billingDetails[] = [
                        'supplier_id' => $supplierId,
                        'purpose' => $request->purpose[$i] ?? '',
                        'vat' => $request->vat[$i] ?? 0,
                        'vat_amount' => $va,
                        'total_payable' => $tp,
                    ];
                    $totalPayable += (float) $tp;
                    $vatAmt += (float) $va;
                }
            }
        }

        // Calculate net profit
        $invAmt = $request->amount_usd * $exchangeRate;
        $totalPayable = round($totalPayable, 3);
        $netProfit = round($invAmt - $totalPayable, 3);

        Bill::create([
            'invoice_id' => $invoice->id,
            'bill_number' => $billNumber,
            'amount' => $invAmt,
            'amount_usd' => $request->amount_usd,
            'exchange_rate' => $exchangeRate,
            'amount_omr' => $invAmt,
            'bill_date' => $request->bill_date,
            'status' => 'unpaid',
            'billing_details' => $billingDetails,
            'net_profit' => $netProfit,
        ]);

        // Update invoice status to billed
        $invoice->update(['status' => 'billed']);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully.',
            ]);
        }

        return redirect()->route('bills.index')->with('success', 'Bill created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): JsonResponse
    {
        $customer = $invoice->customer;
        $vehicle = $invoice->booking ? $invoice->booking->vehicle : null;

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'booking_id' => $invoice->booking_id,
                'total' => $invoice->amount,
                'vat' => $invoice->vat,
                'vat_amount' => $invoice->vat_amount,
                'subtotal' => $invoice->subtotal,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'status' => $invoice->status,
                'rate' => $invoice->rate,
                'rate_type' => $invoice->rate_type,
                'extra_kms_charges' => $invoice->extra_kms_charges,
                'security_deposit' => $invoice->security_deposit,
                'insurance_fee' => $invoice->insurance_fee,
                'additional_driver_fee' => $invoice->additional_driver_fee,
                'delivery_charge' => $invoice->delivery_charge,
                'fuel_charge' => $invoice->fuel_charge,
                'gps_charges' => $invoice->gps_charges,
                'salik_toll_charges' => $invoice->salik_toll_charges,
                'discount_amount' => $invoice->discount_amount,
                'customer_name' => $customer ? $customer->name : '',
                'vehicle_name' => $vehicle ? $vehicle->name : '',
                'pickup_datetime' => $invoice->booking?->pickup_datetime?->format('Y-m-d H:i') ?: ($invoice->booking?->from_date?->format('Y-m-d 00:00') ?: ''),
                'return_datetime' => $invoice->booking?->return_datetime?->format('Y-m-d H:i') ?: ($invoice->booking?->to_date?->format('Y-m-d 00:00') ?: ''),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'total' => 'required|numeric|min:0.01',
            'vat' => 'required|numeric|min:0',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'rate' => 'nullable|numeric|min:0',
            'rate_type' => 'nullable|in:daily,weekly,monthly',
            'extra_kms_charges' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'additional_driver_fee' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'fuel_charge' => 'nullable|numeric|min:0',
            'gps_charges' => 'nullable|numeric|min:0',
            'salik_toll_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0|max:100',
        ]);

        $booking = $invoice->booking;
        $rentalCharge = 0;
        if ($booking && isset($validated['rate']) && isset($validated['rate_type'])) {
            $rentalCharge = $this->calculateRentalCharge($booking, (float) $validated['rate'], $validated['rate_type']);
        } elseif (isset($validated['rate'])) {
            $rentalCharge = (float) $validated['rate'];
        }

        // Calculate charges total (sum of all charges + rental charge)
        $chargesTotal = max(0,
            $rentalCharge
            + ($validated['extra_kms_charges'] ?? 0)
            + ($validated['security_deposit'] ?? 0)
            + ($validated['insurance_fee'] ?? 0)
            + ($validated['additional_driver_fee'] ?? 0)
            + ($validated['delivery_charge'] ?? 0)
            + ($validated['fuel_charge'] ?? 0)
            + ($validated['gps_charges'] ?? 0)
            + ($validated['salik_toll_charges'] ?? 0)
        );

        // Calculate discount amount as percentage of charges total
        $discountPercent = $validated['discount_amount'] ?? 0;
        $discountAmount = $chargesTotal * ($discountPercent / 100);

        // Total is charges total minus discount
        $total = max(0, $chargesTotal - $discountAmount);

        $vatPercent = $validated['vat'] ?? 0;
        $vatAmount = $total * ($vatPercent / 100);
        $subtotal = $total - $vatAmount;

        $validated['vat_amount'] = $vatAmount;
        $validated['subtotal'] = $subtotal;
        $validated['amount'] = $total;

        $invoice->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully.',
        ]);
    }

    /**
     * Get action buttons HTML
     */
    private function calculateRentalCharge(Booking $booking, float $rate, ?string $rateType): float
    {
        if ($rate <= 0 || ! $rateType) {
            return 0.0;
        }

        $pickup = $booking->pickup_datetime ?: $booking->from_date;
        $return = $booking->return_datetime ?: $booking->to_date;

        if (! $pickup || ! $return || $return <= $pickup) {
            return $rate;
        }

        $diff = $pickup->diff($return);
        $days = (int) $diff->format('%a');
        $hours = (int) $diff->format('%h');

        $dayCount = $days;
        if ($hours > 0 || $days === 0) {
            $dayCount += 1;
        }

        switch ($rateType) {
            case 'daily':
                return $rate * $dayCount;
            case 'weekly':
                return $rate * ($dayCount / 7);
            case 'monthly':
                return $rate * ($dayCount / 30);
            default:
                return $rate;
        }
    }

    private function getActionButtons(Invoice $invoice): string
    {
        $buttons = '
            <button type="button" class="btn btn-info btn-sm view-invoice-btn mr-1" data-toggle="tooltip" title="View" data-id="'.$invoice->id.'" data-url="'.route('invoices.show', $invoice).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm edit-invoice-btn mr-1" data-toggle="tooltip" title="Edit" data-id="'.$invoice->id.'" data-url="'.route('invoices.edit', $invoice).'">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm delete-invoice-btn" data-toggle="tooltip" title="Delete" data-id="'.$invoice->id.'" data-url="'.route('invoices.destroy', $invoice).'">
                <i class="fas fa-trash"></i>
            </button>
        ';

        return $buttons;
    }
}
