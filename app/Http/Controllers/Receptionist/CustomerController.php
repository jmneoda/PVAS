<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX  –  GET /receptionist/customers
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $search        = $request->input('search');
        $selectedPeriod = $request->input('period', 'all');

        /* ── Build query ── */
        $query = Customer::query()->orderByDesc('created_at');

        /* ── Period filter ── */
        $query->when($selectedPeriod && $selectedPeriod !== 'all', function ($q) use ($selectedPeriod) {
            $now = Carbon::now();
            match ($selectedPeriod) {
                'today'      => $q->whereDate('created_at', $now->toDateString()),
                'yesterday'  => $q->whereDate('created_at', $now->subDay()->toDateString()),
                'this_week'  => $q->whereBetween('created_at', [$now->startOfWeek(), Carbon::now()->endOfWeek()]),
                'this_month' => $q->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year),
                'this_year'  => $q->whereYear('created_at', $now->year),
                default      => null,
            };
        });

        /* ── Search by name ── */
        $query->when($search, function ($q) use ($search) {
            $q->where(function ($inner) use ($search) {
                $inner->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name',  'like', "%{$search}%");
            });
        });

        $customers      = $query->get();
        $totalCustomers = $customers->count();

        return view('receptionist.customers.index', compact(
            'customers',
            'totalCustomers',
            'search',
            'selectedPeriod'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE  –  POST /receptionist/customers
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'nullable|email|unique:customers,email|max:255',
            'contact_number' => 'required|string|max:255',
            'address'        => 'nullable|string',
        ]);

        $validated['registered_by'] = Auth::id();

        Customer::create($validated);

        return redirect()
            ->route('receptionist.customers.index')
            ->with('success', 'Customer added successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE  –  PUT /receptionist/customers/{customer}
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'nullable|email|unique:customers,email,' . $customer->id . '|max:255',
            'contact_number' => 'required|string|max:255',
            'address'        => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()
            ->route('receptionist.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY  –  DELETE /receptionist/customers/{customer}
    |--------------------------------------------------------------------------
    */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('receptionist.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}