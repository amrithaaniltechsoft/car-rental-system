<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicle;
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
        $vehicles  = Vehicle::where('status', 'available')->get();
        $customers = Customer::all();

        return view('adminlte.bookings.index', compact('vehicles', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $vehicles = Vehicle::where('status', 'available')->get();
        $customers = Customer::all();

        return view('adminlte.bookings.create', compact('vehicles', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id'   => 'required|exists:vehicles,id',
            'customer_id'  => 'required|exists:customers,id',
            'from_date'    => 'required|date',
            'to_date'      => 'required|date|after_or_equal:from_date',
            'total_amount' => 'nullable|numeric|min:0',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
            'notes'        => 'nullable|string',
        ]);

        $booking = Booking::create($request->all());

        // Update vehicle status if booking is confirmed
        if ($request->status === 'confirmed') {
            $booking->vehicle->update(['status' => 'booked']);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): View
    {
        return view('adminlte.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking): View
    {
        $vehicles = Vehicle::all();
        $customers = Customer::all();

        return view('adminlte.bookings.edit', compact('booking', 'vehicles', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'required|exists:customers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $booking->status;
        $booking->update($request->all());

        // Update vehicle status based on booking status change
        if ($oldStatus !== 'confirmed' && $request->status === 'confirmed') {
            $booking->vehicle->update(['status' => 'booked']);
        } elseif ($oldStatus === 'confirmed' && $request->status !== 'confirmed') {
            $booking->vehicle->update(['status' => 'available']);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->status === 'confirmed') {
            $booking->vehicle->update(['status' => 'available']);
        }

        $booking->delete();

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
            'completed' => '<span class="badge badge-success">Completed</span>',
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
            <a href="javascript:void(0)" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i>
            </a>
            <a href="javascript:void(0)" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i>
            </a>
            <form action="' . route('bookings.destroy', $booking) . '" method="POST" style="display: inline;">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        ';
    }
}
