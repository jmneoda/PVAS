<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort   = $request->input('sort', 'created_at');
        $dir    = $request->input('dir', 'desc');

        $allowedSorts = ['first_name', 'last_name', 'email', 'contact_number', 'created_at'];
        if (! in_array($sort, $allowedSorts)) $sort = 'created_at';
        if (! in_array($dir, ['asc', 'desc'])) $dir = 'desc';

        $query = Customer::with(['pets', 'appointments'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name',       'like', "%{$search}%")
                          ->orWhere('last_name',      'like', "%{$search}%")
                          ->orWhere('email',          'like', "%{$search}%")
                          ->orWhere('contact_number', 'like', "%{$search}%")
                          ->orWhere('address',        'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $dir);

        $customers      = $query->paginate(15)->withQueryString();
        $totalCustomers = Customer::count();

        return view('customers.index', compact(
            'customers',
            'search',
            'sort',
            'dir',
            'totalCustomers'
        ));
    }

    // ── Show ──────────────────────────────────────────────────────────
    public function show(Customer $customer)
    {
        $customer->load(['pets', 'appointments', 'registeredBy']);

        if (request()->ajax()) {
            return response()->json($customer);
        }

        return view('customers.show', compact('customer'));
    }

    // ── Create ────────────────────────────────────────────────────────
    public function create()
    {
        return view('customers.create');
    }

    // ── Store ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'nullable|email|max:150|unique:customers,email',
            'contact_number' => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
        ]);

        $validated['registered_by'] = Auth::id();

        $customer = Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer ' . $customer->full_name . ' has been registered successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    // ── Update ────────────────────────────────────────────────────────
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => ['nullable', 'email', 'max:150', Rule::unique('customers', 'email')->ignore($customer->id)],
            'contact_number' => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer ' . $customer->full_name . ' has been updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────
    public function destroy(Customer $customer)
    {
        $name = $customer->full_name;
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer ' . $name . ' has been removed.');
    }
}