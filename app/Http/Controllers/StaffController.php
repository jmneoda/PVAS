<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StaffController extends Controller
{
    // ── Human-readable labels keyed by stored role value ──────────────
    public static array $roleLabels = [
        'veterinarian'  => 'Veterinarian',
        'receptionist'  => 'Receptionist',
        'vet_nurse'     => 'Vet Nurse',
        'vet_assistant' => 'Vet Assistant',
        'groomer'       => 'Groomer',
    ];

    // ── Index ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        try {
            Carbon::createFromFormat('Y-m', $selectedMonth);
        } catch (\Exception $e) {
            $selectedMonth = now()->format('Y-m');
        }

        $roleFilter = $request->input('role_filter', 'all');

        $query = DB::table('users')
            ->where('is_active', 1)
            ->whereIn('role', array_keys(self::$roleLabels))
            ->orderBy('name');

        if ($roleFilter !== 'all' && array_key_exists($roleFilter, self::$roleLabels)) {
            $query->where('role', $roleFilter);
        }

        $staffList = $query->select(
            'id', 'name', 'email', 'phone_number', 'role', 'created_at'
        )->get();

        // Count per role for the filter row
        $roleCounts = DB::table('users')
            ->where('is_active', 1)
            ->whereIn('role', array_keys(self::$roleLabels))
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        return view('staff', compact(
            'staffList',
            'selectedMonth',
            'roleFilter',
            'roleCounts'
        ));
    }

    // ── Store (Add Staff) ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'phone_number'      => 'nullable|string|max:20',
            'role'              => ['required', Rule::in(array_keys(self::$roleLabels))],
            'password'          => 'required|string|min:8|confirmed',
        ], [
            'name.required'     => 'Staff name is required.',
            'email.required'    => 'Email address is required.',
            'email.unique'      => 'This email is already registered.',
            'role.required'     => 'Please assign a role.',
            'password.min'      => 'Password must be at least 8 characters.',
            'password.confirmed'=> 'Passwords do not match.',
        ]);

        DB::table('users')->insert([
            'name'         => trim($request->name),
            'email'        => strtolower(trim($request->email)),
            'phone_number' => $request->phone_number,
            'role'         => $request->role,
            'password'     => Hash::make($request->password),
            'is_active'    => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    // ── Update ────────────────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone_number' => 'nullable|string|max:20',
            'role'         => ['required', Rule::in(array_keys(self::$roleLabels))],
        ]);

        DB::table('users')->where('id', $id)->update([
            'name'         => trim($request->name),
            'email'        => strtolower(trim($request->email)),
            'phone_number' => $request->phone_number,
            'role'         => $request->role,
            'updated_at'   => now(),
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    // ── Destroy (soft-delete via is_active) ───────────────────────────
    public function destroy(string $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if ($user && $user->role === 'admin') {
            return redirect()->route('staff.index')
                ->with('error', 'Admin accounts cannot be removed here.');
        }

        DB::table('users')->where('id', $id)->update([
            'is_active'  => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}