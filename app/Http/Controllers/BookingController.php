<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
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
        $vehicles  = $this->vehiclesForSelect();
        $customers = $this->customersForSelect();

        return view('adminlte.bookings.index', compact('vehicles', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $vehicles  = $this->vehiclesForSelect();
        $customers = $this->customersForSelect();

        return view('adminlte.bookings.create', compact('vehicles', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'vehicle_id'   => 'required|exists:vehicles,id',
            'customer_id'  => 'required|exists:customers,id',
            'from_date'    => 'required|date',
            'to_date'      => 'required|date|after_or_equal:from_date',
            'total_amount' => 'nullable|numeric|min:0',
            'status'       => 'required|in:pending,confirmed,on_hold,cancelled',
            'notes'        => 'nullable|string',
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
            ->where('status', 'confirmed')
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

        $booking = Booking::create($request->all());

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
        $vehicles  = $this->vehiclesForSelect();
        $customers = $this->customersForSelect();

        return view('adminlte.bookings.edit', compact('booking', 'vehicles', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'required|exists:customers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,on_hold,cancelled',
            'notes' => 'nullable|string',
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
            'vehicle_id',
            'customer_id',
            'from_date',
            'to_date',
            'status',
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'id';

        $query = Booking::with(['vehicle', 'customer']);

        if (! empty($searchValue)) {
            $query->whereHas('customer', function ($q) use ($searchValue) {
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
                'vehicle' => $booking->vehicle->name . ' (' . $booking->vehicle->registration_number . ')',
                'customer' => $booking->customer->name,
                'dates' => $booking->from_date->format('d/m/Y') . ' - ' . $booking->to_date->format('d/m/Y'),
                'status' => $this->getStatusBadge($booking->status),
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
    private function getStatusBadge(string $status): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'confirmed' => '<span class="badge badge-primary">Confirmed</span>',
            'on_hold' => '<span class="badge badge-info">On Hold</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons(Booking $booking): string
    {
        return '
            <button type="button" class="btn btn-info btn-sm view-booking-btn" data-url="'.route('bookings.show', $booking).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm edit-booking-btn" data-id="'.$booking->id.'">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm delete-booking-btn" data-id="'.$booking->id.'" data-url="'.route('bookings.destroy', $booking).'">
                <i class="fas fa-trash"></i>
            </button>
        ';
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
            ->where('status', 'confirmed')
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
                : 'Vehicle is not available for the selected dates. It has a confirmed booking during this period.',
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
            ],
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
