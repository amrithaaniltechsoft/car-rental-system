<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('adminlte.customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('adminlte.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_type'           => 'required|in:individual,company',
            'name'                    => 'required_if:customer_type,individual|nullable|string|max:255',
            'company_name'            => 'required_if:customer_type,company|nullable|string|max:255',
            'address'                 => 'required|string',
            'phone_number'            => 'required|string|max:20',
            'id_card_number'          => 'required_if:customer_type,individual|nullable|string|max:50|unique:customers,id_card_number',
            'company_registration_id' => 'required_if:customer_type,company|nullable|string|max:50|unique:customers,company_registration_id',
        ]);

        $data = $request->all();
        if ($request->customer_type === 'company') {
            $data['name'] = $request->company_name;
            $data['id_card_number'] = null;
        } else {
            $data['company_name'] = null;
            $data['company_registration_id'] = null;
        }

        Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        if (request()->ajax()) {
            return view('adminlte.customers.show_modal', compact('customer'));
        }

        return view('adminlte.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        //return view('adminlte.customers.edit', compact('customer'));

        if (request()->ajax()) {
            return view('adminlte.customers.edit_modal', compact('customer'));
        }
        return view('adminlte.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'customer_type'           => 'required|in:individual,company',
            'name'                    => 'required_if:customer_type,individual|nullable|string|max:255',
            'company_name'            => 'required_if:customer_type,company|nullable|string|max:255',
            'address'                 => 'required|string',
            'phone_number'            => 'required|string|max:20',
            'id_card_number'          => 'required_if:customer_type,individual|nullable|string|max:50|unique:customers,id_card_number,' . $customer->id,
            'company_registration_id' => 'required_if:customer_type,company|nullable|string|max:50|unique:customers,company_registration_id,' . $customer->id,
        ]);

        $data = $request->all();
       
        if ($request->customer_type === 'company') {
            $data['name'] = $request->company_name;
            $data['id_card_number'] = null;
        } else {
            $data['company_name'] = null;
            $data['company_registration_id'] = null;
        }

        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse|JsonResponse
    {
        if ($customer->bookings()->exists()) {
            $message = 'Cannot delete this customer because they are in use by existing bookings.';

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('customers.index')
                ->with('error', $message);
        }

        $customer->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully.',
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Get customers data for DataTables AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        $columns = [
            'id',
            'customer_type',
            'name',
            'company_name',
            'address',
            'phone_number',
            'id_card_number',
            'company_registration_id',
        ];

        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        
        // Map table columns to DB columns
        $tableColumnsMap = [
            0 => 'id',
            1 => 'customer_type',
            2 => 'name',
            3 => 'address',
            4 => 'phone_number',
            5 => 'id_card_number',
        ];
        $orderColumn = $tableColumnsMap[$orderColumnIndex] ?? 'id';

        $query = Customer::query()->select($columns);

        if (! empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('company_name', 'like', "%{$searchValue}%")
                    ->orWhere('phone_number', 'like', "%{$searchValue}%")
                    ->orWhere('id_card_number', 'like', "%{$searchValue}%")
                    ->orWhere('company_registration_id', 'like', "%{$searchValue}%")
                    ->orWhere('address', 'like', "%{$searchValue}%");
            });
        }

        $recordsTotal = Customer::count();
        $recordsFiltered = (clone $query)->count();

        $customers = $query
            ->orderBy($orderColumn, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];
        $rowNum = $start + 1;

        foreach ($customers as $customer) {
            $data[] = [
                'id' => $rowNum++,
                'customer_type' => ucfirst($customer->customer_type),
                'name' => $customer->customer_type === 'company' ? $customer->company_name : $customer->name,
                'address' => $customer->address,
                'phone_number' => $customer->phone_number,
                'id_card_number' => $customer->customer_type === 'company' ? $customer->company_registration_id : $customer->id_card_number,
                'actions' => $this->getActionButtons($customer),
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
     * Get action buttons HTML
     */
    private function getActionButtons(Customer $customer): string
    {
        return '
            <button type="button" class="btn btn-info btn-sm view-customer-btn" data-url="'.route('customers.show', $customer).'">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm edit-customer-btn" data-url="'.route('customers.edit', $customer).'">
                <i class="fas fa-edit"></i>
            </button>
            <form action="'.route('customers.destroy', $customer).'" method="POST" style="display: inline;">
                '.csrf_field().'
                '.method_field('DELETE').'
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        ';
    }
}
