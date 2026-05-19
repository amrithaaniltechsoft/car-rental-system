<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\FuelType;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $fuelTypes = FuelType::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();

        return view('adminlte.vehicles.index', compact('fuelTypes', 'brands', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $fuelTypes = FuelType::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();

        return view('adminlte.vehicles.create', compact('fuelTypes', 'brands', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|integer|between:2000,2100',
            'brand' => 'required|exists:brands,name',
            'type' => 'required|exists:vehicle_types,name',
            'registration_number' => 'required|string|max:255|unique:vehicles',
            'fuel_type' => 'required|exists:fuel_types,name',
            'seating_capacity' => 'required|integer|min:1',
            'rc_book_details' => 'nullable|string',
            'insurance_details' => 'nullable|string',
        ]);

        Vehicle::create($request->all());

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle): View
    {
        return view('adminlte.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle): View
    {
        $fuelTypes = FuelType::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();

        return view('adminlte.vehicles.edit', compact('vehicle', 'fuelTypes', 'brands', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|integer|between:2000,2100',
            'brand' => 'required|exists:brands,name',
            'type' => 'required|exists:vehicle_types,name',
            'registration_number' => 'required|string|max:255|unique:vehicles,registration_number,'.$vehicle->id,
            'fuel_type' => 'required|exists:fuel_types,name',
            'seating_capacity' => 'required|integer|min:1',
            'rc_book_details' => 'nullable|string',
            'insurance_details' => 'nullable|string',
            'status' => 'required|in:available,booked,maintenance,inactive',
        ]);

        $vehicle->update($request->all());

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Get vehicles data for DataTables AJAX with optimizations
     */
    public function getData(Request $request): JsonResponse
    {
        $columns = [
            'id',
            'name',
            'model',
            'brand',
            'type',
            'registration_number',
            'fuel_type',
            'seating_capacity',
            'status',
        ];

        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';

        // Only select needed columns - HUGE performance boost
        $query = Vehicle::query()->select($columns);

        if (! empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('model', 'like', "%{$searchValue}%")
                    ->orWhere('brand', 'like', "%{$searchValue}%")
                    ->orWhere('registration_number', 'like', "%{$searchValue}%")
                    ->orWhere('fuel_type', '=', $searchValue); // Exact match for fuel type
            });
        }

        // Count before filtering is unnecessary in most cases
        $recordsTotal = Vehicle::count();
        $recordsFiltered = (clone $query)->count(); // Clone to avoid pagination in count

        $vehicles = $query
            ->orderBy($orderColumn, $orderDir)
            ->offset($start) // offset() is more explicit than skip()
            ->limit($length) // limit() is more explicit than take()
            ->get();

        $data = [];
        $rowNum = $start + 1;

        foreach ($vehicles as $vehicle) {
            $data[] = [
                'id' => $rowNum++,
                'name' => $vehicle->name,
                'model' => $vehicle->model,
                'brand' => $vehicle->brand,
                'type' => ucfirst($vehicle->type),
                'registration_number' => $vehicle->registration_number,
                'fuel_type' => ucfirst($vehicle->fuel_type),
                'seating_capacity' => $vehicle->seating_capacity,
                'status' => $this->getStatusBadge($vehicle->status),
                'actions' => $this->getActionButtons($vehicle),
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
            'available' => '<span class="badge badge-success">Available</span>',
            'booked' => '<span class="badge badge-warning">Booked</span>',
            'maintenance' => '<span class="badge badge-danger">Maintenance</span>',
            'inactive' => '<span class="badge badge-secondary">Inactive</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons(Vehicle $vehicle): string
    {
        return '
            <a href="javascript:void(0)" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i>
            </a>
            <a href="javascript:void(0)" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i>
            </a>
            <form action="'.route('vehicles.destroy', $vehicle).'" method="POST" style="display: inline;">
                '.csrf_field().'
                '.method_field('DELETE').'
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        ';
    }
}
