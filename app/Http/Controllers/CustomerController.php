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
            'customer_type' => 'required|in:individual,company',
            'first_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'last_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'company_name' => 'required_if:customer_type,company|nullable|string|max:255',
            'address' => 'required_if:customer_type,company|nullable|string',
            'residential_address' => 'required_if:customer_type,individual|nullable|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'passport_number' => 'nullable|string|max:50',
            'driving_license_number' => 'required_if:customer_type,individual|nullable|string|max:50',
            'license_expiry_date' => 'required_if:customer_type,individual|nullable|date',
            'license_issue_country' => 'required_if:customer_type,individual|nullable|string|max:255',
        ]);

        $data = $request->all();
        if ($request->customer_type === 'company') {
            $data['name'] = $request->company_name;
            $data['first_name'] = null;
            $data['last_name'] = null;
            $data['date_of_birth'] = null;
            $data['nationality'] = null;
            $data['residential_address'] = null;
            $data['passport_number'] = null;
            $data['driving_license_number'] = null;
            $data['license_expiry_date'] = null;
            $data['license_issue_country'] = null;
        } else {
            $data['name'] = $request->first_name.' '.$request->last_name;
            $data['company_name'] = null;
            $data['address'] = $request->residential_address;
        }

        // Generate custom customer ID: CUYearMonthDaySequence
        $today = now()->format('Ymd');
        $prefix = 'CU'.$today;

        // Get the last customer created today with this prefix
        $lastCustomer = Customer::where('customer_id', 'like', $prefix.'%')
            ->orderBy('customer_id', 'desc')
            ->first();

        if ($lastCustomer) {
            // Extract sequence number and increment
            $lastSequence = (int) substr($lastCustomer->customer_id, -3);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        $data['customer_id'] = $prefix.str_pad($newSequence, 3, '0', STR_PAD_LEFT);

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
        // return view('adminlte.customers.edit', compact('customer'));

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
            'customer_type' => 'required|in:individual,company',
            'first_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'last_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'company_name' => 'required_if:customer_type,company|nullable|string|max:255',
            'address' => 'required_if:customer_type,company|nullable|string',
            'residential_address' => 'required_if:customer_type,individual|nullable|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'passport_number' => 'nullable|string|max:50',
            'driving_license_number' => 'required_if:customer_type,individual|nullable|string|max:50',
            'license_expiry_date' => 'required_if:customer_type,individual|nullable|date',
            'license_issue_country' => 'required_if:customer_type,individual|nullable|string|max:255',
        ]);

        $data = $request->all();

        if ($request->customer_type === 'company') {
            $data['name'] = $request->company_name;
            $data['first_name'] = null;
            $data['last_name'] = null;
            $data['date_of_birth'] = null;
            $data['nationality'] = null;
            $data['residential_address'] = null;
            $data['passport_number'] = null;
            $data['driving_license_number'] = null;
            $data['license_expiry_date'] = null;
            $data['license_issue_country'] = null;
        } else {
            $data['name'] = $request->first_name.' '.$request->last_name;
            $data['company_name'] = null;
            $data['address'] = $request->residential_address;
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
            'customer_id',
            'customer_type',
            'name',
            'company_name',
            'address',
            'phone_number',
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
            1 => 'customer_id',
            2 => 'customer_type',
            3 => 'name',
            4 => 'address',
            5 => 'phone_number',
        ];
        $orderColumn = $tableColumnsMap[$orderColumnIndex] ?? 'id';

        $query = Customer::query()->select($columns);

        if (! empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('company_name', 'like', "%{$searchValue}%")
                    ->orWhere('phone_number', 'like', "%{$searchValue}%")
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
                'customer_id' => $customer->customer_id ?: 'N/A',
                'customer_type' => ucfirst($customer->customer_type),
                'name' => $customer->customer_type === 'company' ? $customer->company_name : $customer->name,
                'address' => $customer->address,
                'phone_number' => $customer->phone_number,
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
