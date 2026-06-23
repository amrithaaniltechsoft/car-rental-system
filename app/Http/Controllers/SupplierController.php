<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $supplierNames = Supplier::whereNotNull('name')->pluck('name');

        return view('adminlte.suppliers.index', compact('supplierNames'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('adminlte.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $data = $request->all();

        // Generate supplier code: SL + YYYYMMDD + 3-digit sequence
        $today = now()->format('Ymd');
        $prefix = 'SL'.$today;
        $lastSupplier = Supplier::where('supplier_code', 'like', $prefix.'%')
            ->orderBy('supplier_code', 'desc')
            ->first();
        if ($lastSupplier) {
            $lastSequence = (int) substr($lastSupplier->supplier_code, -3);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        $data['supplier_code'] = $prefix.str_pad($newSequence, 3, '0', STR_PAD_LEFT);

        Supplier::create($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully.',
            ]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified resource or return JSON for AJAX.
     */
    public function show(Supplier $supplier): View|JsonResponse
    {
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'supplier_code' => $supplier->supplier_code,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'created_at' => $supplier->created_at->format('d M Y'),
            ]);
        }

        return view('adminlte.suppliers.show', compact('supplier'));
    }

    /**
     * Show the edit form or return JSON for AJAX.
     */
    public function edit(Supplier $supplier): View|JsonResponse
    {
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'id' => $supplier->id,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
            ]);
        }

        return view('adminlte.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($request->all());

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully.',
            ]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse|JsonResponse
    {
        $supplier->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully.',
            ]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Get suppliers data for DataTables AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        $columns = [
            'id',
            'supplier_code',
            'name',
            'phone',
            'email',
            'address',
        ];

        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $filterName = $request->input('filter_name');
        $filterEmail = $request->input('filter_email');
        $filterPhone = $request->input('filter_phone');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';

        $query = Supplier::query()->select($columns);

        if (! empty($filterName)) {
            $query->where('name', 'like', "%{$filterName}%");
        }
        if (! empty($filterEmail)) {
            $query->where('email', 'like', "%{$filterEmail}%");
        }
        if (! empty($filterPhone)) {
            $query->where('phone', 'like', "%{$filterPhone}%");
        }

        $recordsTotal = Supplier::count();
        $recordsFiltered = (clone $query)->count();

        $suppliers = $query
            ->orderBy($orderColumn, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];
        foreach ($suppliers as $supplier) {
            $data[] = [
                'id' => $supplier->id,
                'supplier_code' => $supplier->supplier_code ?? 'N/A',
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'email' => $supplier->email ?? 'N/A',
                'address' => $supplier->address ?? 'N/A',
                'actions' => $this->getActionButtons($supplier),
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
     * Generate action buttons for DataTables
     */
    private function getActionButtons(Supplier $supplier): string
    {
        $showBtn = '<button class="btn btn-sm btn-info mr-1" onclick="showSupplier('.$supplier->id.')" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>';
        $editBtn = '<button class="btn btn-sm btn-warning mr-1" onclick="editSupplier('.$supplier->id.')" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>';
        $deleteBtn = '<button class="btn btn-sm btn-danger" onclick="deleteSupplier('.$supplier->id.')" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>';

        return '<div class="btn-group">'.$showBtn.' '.$editBtn.' '.$deleteBtn.'</div>';
    }
}
