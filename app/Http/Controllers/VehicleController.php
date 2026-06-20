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
        $vehicleNames = Vehicle::distinct()->orderBy('name')->pluck('name');
        $numberPlates = Vehicle::distinct()->orderBy('number_plate')->pluck('number_plate');
        $numberCodes = Vehicle::distinct()->orderBy('number_code')->pluck('number_code');

        return view('adminlte.vehicles.index', compact('fuelTypes', 'brands', 'types', 'vehicleNames', 'numberPlates', 'numberCodes'));
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
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|integer|between:2000,2100',
            'brand' => 'required|exists:brands,name',
            'type' => 'required|exists:vehicle_types,name',
            'number_plate' => 'required|string|max:255|unique:vehicles',
            'number_code' => 'required|string|max:255|unique:vehicles',
            'fuel_type' => 'required|exists:fuel_types,name',
            'seating_capacity' => 'required|integer|min:1',
            'rc_book_details' => 'nullable|string',
            'insurance_details' => 'nullable|string',
        ]);

        Vehicle::create($request->all());

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle created successfully.',
            ]);
        }

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle): View
    {
        if (request()->ajax()) {
            return view('adminlte.vehicles.show_modal', compact('vehicle'));
        }

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

        if (request()->ajax()) {
            return view('adminlte.vehicles.edit_modal', compact('vehicle', 'fuelTypes', 'brands', 'types'));
        }

        return view('adminlte.vehicles.edit', compact('vehicle', 'fuelTypes', 'brands', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|integer|between:2000,2100',
            'brand' => 'required|exists:brands,name',
            'type' => 'required|exists:vehicle_types,name',
            'number_plate' => 'required|string|max:255|unique:vehicles,number_plate,'.$vehicle->id,
            'number_code' => 'required|string|max:255|unique:vehicles,number_code,'.$vehicle->id,
            'fuel_type' => 'required|exists:fuel_types,name',
            'seating_capacity' => 'required|integer|min:1',
            'rc_book_details' => 'nullable|string',
            'insurance_details' => 'nullable|string',
        ]);

        $vehicle->update($request->all());

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully.',
            ]);
        }

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
            'number_plate',
            'number_code',
            'fuel_type',
            'seating_capacity',
        ];

        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $filterName = $request->input('filter_name');
        $filterBrand = $request->input('filter_brand');
        $filterType = $request->input('filter_type');
        $filterPlate = $request->input('filter_plate');
        $filterCode = $request->input('filter_code');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';

        $query = Vehicle::query()->select($columns);

        if (! empty($filterName)) {
            $query->where('name', 'like', "%{$filterName}%");
        }
        if (! empty($filterBrand)) {
            $query->where('brand', 'like', "%{$filterBrand}%");
        }
        if (! empty($filterType)) {
            $query->where('type', 'like', "%{$filterType}%");
        }
        if (! empty($filterPlate)) {
            $query->where('number_plate', 'like', "%{$filterPlate}%");
        }
        if (! empty($filterCode)) {
            $query->where('number_code', 'like', "%{$filterCode}%");
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
                'number_plate' => $vehicle->number_plate,
                'number_code' => $vehicle->number_code,
                'fuel_type' => ucfirst($vehicle->fuel_type),
                'seating_capacity' => $vehicle->seating_capacity,
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
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        if ($vehicle->bookings()->exists()) {
            $message = 'Cannot delete this vehicle because it is in use by existing bookings.';

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('vehicles.index')
                ->with('error', $message);
        }

        $vehicle->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully.',
            ]);
        }

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons(Vehicle $vehicle): string
    {
        return '
            <button type="button" class="btn btn-info btn-sm view-vehicle-btn" data-toggle="tooltip" title="View" data-url="'.route('vehicles.show', $vehicle).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm edit-vehicle-btn" data-toggle="tooltip" title="Edit" data-url="'.route('vehicles.edit', $vehicle).'">
                <i class="fas fa-edit"></i>
            </button>
            <form action="'.route('vehicles.destroy', $vehicle).'" method="POST" style="display: inline;">
                '.csrf_field().'
                '.method_field('DELETE').'
                <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Delete" onclick="return confirm(\'Are you sure?\')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        ';
    }
}
