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
        $bookingIds = Booking::distinct()->orderBy('booking_id')->pluck('booking_id')->filter()->values();

        return view('adminlte.bookings.index', compact('vehicles', 'customers', 'bookingIds'));
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
            'booking_date' => 'required|date',
            'pickup_datetime' => 'required|date',
            'return_datetime' => 'required|date|after_or_equal:pickup_datetime',
            'pickup_location' => 'nullable|string|max:255',
            'return_location' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        if ($validator->fails()) {

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check vehicle availability
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $fromDate = date('Y-m-d', strtotime($request->pickup_datetime));
        $toDate = date('Y-m-d', strtotime($request->return_datetime));

        $hasConflict = Booking::where('vehicle_id', $vehicle->id)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate])
                    ->orWhere(function ($q) use ($fromDate, $toDate) {
                        $q->where('from_date', '<=', $fromDate)->where('to_date', '>=', $toDate);
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

            return redirect()->back()->withInput()->with('error', 'Vehicle is not available for the selected dates. Try on other date.');
        }

        $data = $request->only([
            'vehicle_id',
            'customer_id',
            'booking_date',
            'pickup_datetime',
            'return_datetime',
            'pickup_location',
            'return_location',
            'status',
        ]);

        // Keep legacy date fields in sync
        $data['from_date'] = $fromDate;
        $data['to_date'] = $toDate;

        // Compute rental duration
        $pickup = new \DateTime($request->pickup_datetime);
        $return = new \DateTime($request->return_datetime);
        $diff = $pickup->diff($return);
        $days = (int) $diff->format('%a');
        $hours = (int) $diff->format('%h');
        if ($days > 0) {
            $data['rental_duration'] = $days.' day'.($days > 1 ? 's' : '').($hours > 0 ? ' '.$hours.'h' : '');
        } else {
            $data['rental_duration'] = $hours.' hour'.($hours !== 1 ? 's' : '');
        }

        // Generate booking ID
        $today = now()->format('Ymd');
        $prefix = 'BK'.$today;
        $last = Booking::where('booking_id', 'like', $prefix.'%')->orderBy('booking_id', 'desc')->first();
        $sequence = $last ? (int) substr($last->booking_id, -3) + 1 : 1;
        $data['booking_id'] = $prefix.str_pad($sequence, 3, '0', STR_PAD_LEFT);

        Booking::create($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking created successfully.']);
        }

        return redirect()->route('bookings.index')->with('success', 'Booking created successfully.');
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
                return response()->json(['success' => false, 'message' => 'Booking cannot be edited as it has an invoice.'], 422);
            }

            return redirect()->back()->with('error', 'Booking cannot be edited as it has an invoice.');
        }

        $validator = \Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'required|exists:customers,id',
            'booking_date' => 'required|date',
            'pickup_datetime' => 'required|date',
            'return_datetime' => 'required|date|after_or_equal:pickup_datetime',
            'pickup_location' => 'nullable|string|max:255',
            'return_location' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fromDate = date('Y-m-d', strtotime($request->pickup_datetime));
        $toDate = date('Y-m-d', strtotime($request->return_datetime));

        // Check availability if vehicle/dates changed
        if ($request->vehicle_id != $booking->vehicle_id ||
            $fromDate !== optional($booking->from_date)->format('Y-m-d') ||
            $toDate !== optional($booking->to_date)->format('Y-m-d')) {

            $hasConflict = Booking::where('vehicle_id', $request->vehicle_id)
                ->where('id', '!=', $booking->id)
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('from_date', [$fromDate, $toDate])
                        ->orWhereBetween('to_date', [$fromDate, $toDate])
                        ->orWhere(function ($q) use ($fromDate, $toDate) {
                            $q->where('from_date', '<=', $fromDate)->where('to_date', '>=', $toDate);
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

                return redirect()->back()->withInput()->with('error', 'Vehicle is not available for the selected dates. Try on other date.');
            }
        }

        $data = $request->only([
            'vehicle_id',
            'customer_id',
            'booking_date',
            'pickup_datetime',
            'return_datetime',
            'pickup_location',
            'return_location',
            'status',
        ]);
        $data['from_date'] = $fromDate;
        $data['to_date'] = $toDate;

        // Compute rental duration
        $pickup = new \DateTime($request->pickup_datetime);
        $return = new \DateTime($request->return_datetime);
        $diff = $pickup->diff($return);
        $days = (int) $diff->format('%a');
        $hours = (int) $diff->format('%h');
        if ($days > 0) {
            $data['rental_duration'] = $days.' day'.($days > 1 ? 's' : '').($hours > 0 ? ' '.$hours.'h' : '');
        } else {
            $data['rental_duration'] = $hours.' hour'.($hours !== 1 ? 's' : '');
        }

        $booking->update($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking updated successfully.']);
        }

        return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
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
        $filterBookingId = $request->input('filter_booking_id');
        $filterCustomer = $request->input('filter_customer');
        $filterVehicle = $request->input('filter_vehicle');
        $filterFromDate = $request->input('filter_from_date');
        $filterToDate = $request->input('filter_to_date');
        $filterStatus = $request->input('filter_status');
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

        if (! empty($filterBookingId)) {
            $query->where('booking_id', $filterBookingId);
        }
        if (! empty($filterCustomer)) {
            $query->where('customer_id', $filterCustomer);
        }
        if (! empty($filterVehicle)) {
            $query->where('vehicle_id', $filterVehicle);
        }
        if (! empty($filterFromDate)) {
            $query->whereDate('from_date', $filterFromDate);
        }
        if (! empty($filterToDate)) {
            $query->whereDate('to_date', $filterToDate);
        }
        if (! empty($filterStatus)) {
            $query->where('status', $filterStatus);
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
                'vehicle' => $booking->vehicle->name.' ('.$booking->vehicle->number_plate.')',
                'customer' => $booking->customer->name,
                'dates' => ($booking->pickup_datetime ? $booking->pickup_datetime->format('d/m/Y H:i') : ($booking->from_date ? $booking->from_date->format('d/m/Y') : 'N/A'))
                    .' - '
                    .($booking->return_datetime ? $booking->return_datetime->format('d/m/Y H:i') : ($booking->to_date ? $booking->to_date->format('d/m/Y') : 'N/A')),
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
            'pending' => 'warning',
            'confirmed' => 'primary',
            'cancelled' => 'danger',
            'completed' => 'success',
        ];

        $color = $badges[$status] ?? 'secondary';
        $label = ucfirst($status);

        if ($bookingId && $status !== 'confirmed' && $status !== 'completed') {
            return '<span class="badge badge-'.$color.' change-status-btn" data-id="'.$bookingId.'" style="cursor:pointer;">'.$label.'</span>';
        }

        return '<span class="badge badge-'.$color.'">'.$label.'</span>';
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
            <button type="button" class="btn btn-info btn-sm view-booking-btn" data-toggle="tooltip" title="View" data-url="'.route('bookings.show', $booking).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn '.$editClass.' btn-sm edit-booking-btn" data-toggle="tooltip" title="Edit" data-id="'.$booking->id.'" data-has-invoice="'.($hasInvoice ? 'true' : 'false').'" '.$disabledAttr.'>
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn '.$deleteClass.' btn-sm delete-booking-btn" data-toggle="tooltip" title="Delete" data-id="'.$booking->id.'" data-url="'.route('bookings.destroy', $booking).'" data-has-invoice="'.($hasInvoice ? 'true' : 'false').'" '.$disabledAttr.'>
                <i class="fas fa-trash"></i>
            </button>
        ';

        if (! $hasInvoice && $isConfirmed) {
            $pickupStr = $booking->pickup_datetime ? $booking->pickup_datetime->format('Y-m-d H:i') : ($booking->from_date ? $booking->from_date->format('Y-m-d 00:00') : '');
            $returnStr = $booking->return_datetime ? $booking->return_datetime->format('Y-m-d H:i') : ($booking->to_date ? $booking->to_date->format('Y-m-d 00:00') : '');
            $buttons .= '
                <button type="button" class="btn btn-success btn-sm create-invoice-btn" data-toggle="tooltip" title="Add Invoice" data-id="'.$booking->id.'" data-booking-id="'.$booking->booking_id.'" data-vehicle="'.$booking->vehicle->name.'" data-customer="'.$booking->customer->name.'" data-amount="'.$booking->total_amount.'" data-customer-id="'.$booking->customer_id.'" data-from-date="'.$booking->from_date->format('Y-m-d').'" data-pickup-datetime="'.$pickupStr.'" data-return-datetime="'.$returnStr.'">
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
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'description' => 'nullable|string',
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
            'vat' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0|max:100',
        ]);

        $rentalCharge = $this->calculateRentalCharge($booking, (float) ($validated['rate'] ?? 0), $validated['rate_type'] ?? null);

        // Server-side calculation of total, vat_amount and subtotal
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

        $validated['customer_id'] = $booking->customer_id;
        $validated['booking_id'] = $booking->id;
        $validated['invoice_number'] = $this->generateInvoiceNumber();
        $validated['subtotal'] = $subtotal;
        $validated['vat_amount'] = $vatAmount;
        $validated['total'] = $total;
        $validated['amount'] = $total;
        $validated['status'] = 'pending';

        Invoice::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
            ]);
        }

        return redirect()->route('bookings.index')->with('success', 'Invoice created successfully.');
    }

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
            'exclude_booking_id' => 'nullable|integer|exists:bookings,id',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $query = Booking::where('vehicle_id', $vehicle->id)
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate])
                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
                        $q2->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                    });
            });

        if ($request->filled('exclude_booking_id')) {
            $query->where('id', '!=', $request->exclude_booking_id);
        }

        $hasConflict = $query->exists();
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
                'booking_id' => $booking->booking_id,
                'vehicle_id' => $booking->vehicle_id,
                'customer_id' => $booking->customer_id,
                'booking_date' => $booking->booking_date?->format('Y-m-d'),
                'pickup_datetime' => $booking->pickup_datetime?->format('Y-m-d\TH:i'),
                'return_datetime' => $booking->return_datetime?->format('Y-m-d\TH:i'),
                'rental_duration' => $booking->rental_duration,
                'pickup_location' => $booking->pickup_location,
                'return_location' => $booking->return_location,
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

    public function getVehicleDetails(Vehicle $vehicle): JsonResponse
    {
        return response()->json([
            'success' => true,
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'number_plate' => $vehicle->number_plate,
                'number_code' => $vehicle->number_code,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'type' => $vehicle->type,
                'fuel_type' => $vehicle->fuel_type,
                'seating_capacity' => $vehicle->seating_capacity,
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

    /**
     * Get the next booking ID preview for the add form
     */
    public function getNextBookingId(): JsonResponse
    {
        $today = now()->format('Ymd');
        $prefix = 'BK'.$today;
        $last = Booking::where('booking_id', 'like', $prefix.'%')->orderBy('booking_id', 'desc')->first();
        $sequence = $last ? (int) substr($last->booking_id, -3) + 1 : 1;

        return response()->json([
            'booking_id' => $prefix.str_pad($sequence, 3, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Get available vehicles for a given date range
     */
    public function availableVehicles(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'exclude_booking_id' => 'nullable|integer|exists:bookings,id',
        ]);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $bookedVehicleIds = Booking::where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('from_date', [$fromDate, $toDate])
                ->orWhereBetween('to_date', [$fromDate, $toDate])
                ->orWhere(function ($q2) use ($fromDate, $toDate) {
                    $q2->where('from_date', '<=', $fromDate)
                        ->where('to_date', '>=', $toDate);
                });
        })
            ->when($request->filled('exclude_booking_id'), function ($q) use ($request) {
                $q->where('id', '!=', $request->exclude_booking_id);
            })
            ->pluck('vehicle_id')
            ->unique();

        $availableVehicles = Vehicle::whereNotIn('id', $bookedVehicleIds)
            ->select('id', 'name', 'number_plate')
            ->orderBy('name')
            ->get();

        return response()->json($availableVehicles);
    }

    private function vehiclesForSelect(): Collection
    {
        return Vehicle::query()
            ->select('id', 'name', 'number_plate')
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
