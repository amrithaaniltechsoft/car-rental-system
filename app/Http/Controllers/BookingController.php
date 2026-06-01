<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $vehicles = $this->vehiclesForSelect();
        $customers = $this->customersForSelect();

        return view('adminlte.bookings.index', compact('vehicles', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $vehicles = $this->vehiclesForSelect();
        $customers = $this->customersForSelect();

        return view('adminlte.bookings.create', compact('vehicles', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'required|exists:customers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,on_hold,cancelled',
            'notes' => 'nullable|string',
            'payment_type' => 'nullable|in:card,email_credit,lpo,cash',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check vehicle availability for the requested dates
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $hasConflict = Booking::where('vehicle_id', $vehicle->id)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate])
                    ->orWhere(function ($q) use ($fromDate, $toDate) {
                        $q->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                    });
            })
            ->exists();

        if ($hasConflict) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle is not available for the selected dates. Try on other date.',
                    'available' => false,
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Vehicle is not available for the selected dates. Try on other date.');
        }

        $data = $request->all();

        // Generate custom booking ID: BKYearMonthDaySequence
        $today = now()->format('Ymd');
        $prefix = 'BK'.$today;

        // Get the last booking created today with this prefix
        $lastBooking = Booking::where('booking_id', 'like', $prefix.'%')
            ->orderBy('booking_id', 'desc')
            ->first();

        if ($lastBooking) {
            // Extract sequence number and increment
            $lastSequence = (int) substr($lastBooking->booking_id, -3);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        $data['booking_id'] = $prefix.str_pad($newSequence, 3, '0', STR_PAD_LEFT);

        $booking = Booking::create($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.',
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): View
    {
        if (request()->ajax()) {
            return view('adminlte.bookings.show_modal', compact('booking'));
        }

        return view('adminlte.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking): View
    {
        $vehicles = $this->vehiclesForSelect();
        $customers = $this->customersForSelect();

        return view('adminlte.bookings.edit', compact('booking', 'vehicles', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        if ($booking->invoice()->exists()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking cannot be edited as it has an invoice.',
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Booking cannot be edited as it has an invoice.');
        }

        $validator = \Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'required|exists:customers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,on_hold,cancelled',
            'notes' => 'nullable|string',
            'payment_type' => 'nullable|in:card,email_credit,lpo,cash',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check vehicle availability for the requested dates if vehicle or dates changed
        if ($request->vehicle_id != $booking->vehicle_id ||
            $request->from_date != $booking->from_date ||
            $request->to_date != $booking->to_date) {

            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $fromDate = $request->from_date;
            $toDate = $request->to_date;

            $hasConflict = Booking::where('vehicle_id', $vehicle->id)
                ->where('status', 'confirmed')
                ->where('id', '!=', $booking->id)
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('from_date', [$fromDate, $toDate])
                        ->orWhereBetween('to_date', [$fromDate, $toDate])
                        ->orWhere(function ($q) use ($fromDate, $toDate) {
                            $q->where('from_date', '<=', $fromDate)
                                ->where('to_date', '>=', $toDate);
                        });
                })
                ->exists();

            if ($hasConflict) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vehicle is not available for the selected dates. It has a confirmed booking during this period.',
                        'available' => false,
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Vehicle is not available for the selected dates. It has a confirmed booking during this period.');
            }
        }

        $booking->update($request->all());

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully.',
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): RedirectResponse|JsonResponse
    {
        if ($booking->invoice()->exists()) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking cannot be deleted as it has an invoice.',
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Booking cannot be deleted as it has an invoice.');
        }

        $booking->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully.',
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    /**
     * Get bookings data for DataTables AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'id',
            'booking_id',
            'vehicle_id',
            'customer_id',
            'from_date',
            'to_date',
            'status',
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'id';

        $query = Booking::with(['vehicle', 'customer', 'invoice']);

        if (! empty($searchValue)) {
            $query->where('booking_id', 'like', "%{$searchValue}%")
                ->orWhereHas('customer', function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%");
                })->orWhereHas('vehicle', function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('registration_number', 'like', "%{$searchValue}%");
                })->orWhere('status', 'like', "%{$searchValue}%");
        }

        $recordsTotal = Booking::count();
        $recordsFiltered = (clone $query)->count();

        $bookings = $query
            ->orderBy($orderColumn, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];
        $rowNum = $start + 1;

        foreach ($bookings as $booking) {
            $data[] = [
                'id' => $rowNum++,
                'booking_id' => $booking->booking_id ?: 'N/A',
                'vehicle' => $booking->vehicle->name.' ('.$booking->vehicle->registration_number.')',
                'customer' => $booking->customer->name,
                'dates' => $booking->from_date->format('d/m/Y').' - '.$booking->to_date->format('d/m/Y'),
                'status' => $this->getStatusBadge($booking->status, $booking->id),
                'actions' => $this->getActionButtons($booking),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge(string $status, ?int $bookingId = null): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'confirmed' => '<span class="badge badge-primary">Confirmed</span>',
            'on_hold' => '<span class="badge badge-info">On Hold</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
        ];

        $badge = $badges[$status] ?? '<span class="badge badge-secondary">'.ucfirst($status).'</span>';

        if ($bookingId && $status !== 'confirmed') {
            $badge = '<span class="badge badge-'.($status === 'pending' ? 'warning' : ($status === 'on_hold' ? 'info' : 'secondary')).' cursor-pointer change-status-btn" data-id="'.$bookingId.'" style="cursor: pointer;">'.ucfirst(str_replace('_', ' ', $status)).'</span>';
        }

        return $badge;
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons(Booking $booking): string
    {
        $hasInvoice = $booking->invoice()->exists();
        $isConfirmed = $booking->status === 'confirmed';
        $editClass = $hasInvoice ? 'btn-default' : 'btn-warning';
        $deleteClass = $hasInvoice ? 'btn-default' : 'btn-danger';
        $disabledAttr = $hasInvoice ? 'disabled' : '';

        $buttons = '
            <button type="button" class="btn btn-info btn-sm view-booking-btn" data-url="'.route('bookings.show', $booking).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn '.$editClass.' btn-sm edit-booking-btn" data-id="'.$booking->id.'" data-has-invoice="'.($hasInvoice ? 'true' : 'false').'" '.$disabledAttr.'>
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn '.$deleteClass.' btn-sm delete-booking-btn" data-id="'.$booking->id.'" data-url="'.route('bookings.destroy', $booking).'" data-has-invoice="'.($hasInvoice ? 'true' : 'false').'" '.$disabledAttr.'>
                <i class="fas fa-trash"></i>
            </button>
        ';

        if (! $hasInvoice && $isConfirmed) {
            $buttons .= '
                <button type="button" class="btn btn-success btn-sm create-invoice-btn" data-id="'.$booking->id.'" data-booking-id="'.$booking->booking_id.'" data-vehicle="'.$booking->vehicle->name.'" data-customer="'.$booking->customer->name.'" data-amount="'.$booking->total_amount.'" data-customer-id="'.$booking->customer_id.'" data-from-date="'.$booking->from_date->format('Y-m-d').'">
                    <i class="fas fa-file-invoice"></i>
                </button>
            ';
        }

        return $buttons;
    }

    /**
     * Create an invoice for a booking
     */
    public function createInvoice(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        if ($booking->status !== 'confirmed') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice can only be created for confirmed bookings.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Invoice can only be created for confirmed bookings.');
        }

        if ($booking->invoice()->exists()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking already has an invoice.',
                ], 422);
            }

            return redirect()->back()->with('error', 'This booking already has an invoice.');
        }

        $validated = $request->validate([
            'total' => 'required|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'description' => 'nullable|string',
        ]);

        $validated['customer_id'] = $booking->customer_id;
        $validated['booking_id'] = $booking->id;
        $validated['invoice_number'] = $this->generateInvoiceNumber();
        $validated['amount'] = $validated['total'];
        $validated['status'] = 'pending';

        // Calculate subtotal and vat_amount from total and vat percentage
        if (isset($validated['total']) && isset($validated['vat'])) {
            $validated['vat_amount'] = ($validated['total'] * $validated['vat']) / 100;
            $validated['subtotal'] = $validated['total'] - $validated['vat_amount'];
        } else {
            $validated['subtotal'] = $validated['total'];
            $validated['vat_amount'] = 0;
        }

        Invoice::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
            ]);
        }

        return redirect()->route('bookings.index')->with('success', 'Invoice created successfully.');
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV'.now()->format('Ymd');
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

    /**
     * Check vehicle availability for given date range
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Check if vehicle has any confirmed bookings that overlap with the requested dates
        $hasConflict = Booking::where('vehicle_id', $vehicle->id)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate])
                    ->orWhere(function ($q) use ($fromDate, $toDate) {
                        $q->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                    });
            })
            ->exists();

        $isAvailable = ! $hasConflict;

        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable
                ? 'Vehicle is available for the selected dates.'
                : 'Vehicle is not available for the selected dates. Try on other date.',
        ]);
    }

    /**
     * Get booking data for edit modal
     */
    public function getBookingData(Booking $booking): JsonResponse
    {
        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'vehicle_id' => $booking->vehicle_id,
                'customer_id' => $booking->customer_id,
                'from_date' => $booking->from_date->format('Y-m-d'),
                'to_date' => $booking->to_date->format('Y-m-d'),
                'status' => $booking->status,
                'notes' => $booking->notes,
                'payment_type' => $booking->payment_type,
            ],
        ]);
    }

    /**
     * Get customer details for booking form
     */
    public function getCustomerDetails(Customer $customer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'customer_type' => $customer->customer_type,
                'phone_number' => $customer->phone_number,
                'address' => $customer->address,
                'id_card_number' => $customer->id_card_number,
                'company_name' => $customer->company_name,
                'company_registration_id' => $customer->company_registration_id,
            ],
        ]);
    }

    /**
     * Confirm booking status
     */
    public function confirmBooking(Request $request, Booking $booking): JsonResponse
    {
        $booking->update([
            'status' => 'confirmed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated to confirmed successfully.',
        ]);
    }

    private function vehiclesForSelect(): Collection
    {
        return Vehicle::query()
            ->select('id', 'name', 'registration_number')
            ->orderBy('name')
            ->get();
    }

    private function customersForSelect(): Collection
    {
        return Customer::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}
