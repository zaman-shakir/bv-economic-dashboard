<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users (admin only)
     */
    public function index(): View
    {
        $users = User::latest()->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user (admin only)
     */
    public function create(): View
    {
        // Get unique employees from invoices (bypass user access scope for admin)
        $employees = \App\Models\Invoice::withoutGlobalScope('user_access')
            ->whereNotNull('employee_number')
            ->whereNotNull('employee_name')
            ->select('employee_number', 'employee_name')
            ->distinct()
            ->orderBy('employee_name')
            ->get()
            ->map(function($invoice) {
                return [
                    'number' => $invoice->employee_number,
                    'name' => $invoice->employee_name,
                ];
            })
            ->unique('number')
            ->values();

        // Person codes: 2-5 uppercase letters, optionally with numbers at the end (MW, LH, AKS, MB1, etc.)
        $personCodePattern = '/^[A-Z]{2,5}[0-9]{0,2}$/';

        // Fetch person codes separately (guaranteed inclusion of all 9 codes)
        $personCodes = \App\Models\Invoice::withoutGlobalScope('user_access')
            ->whereNotNull('external_reference')
            ->where('external_reference', '!=', '')
            ->select('external_reference')
            ->distinct()
            ->get()
            ->pluck('external_reference')
            ->filter(fn($ref) => preg_match($personCodePattern, $ref) && strlen($ref) < 50)
            ->sort()
            ->values();

        // Fetch other references (limited for performance)
        $otherRefs = \App\Models\Invoice::withoutGlobalScope('user_access')
            ->whereNotNull('external_reference')
            ->where('external_reference', '!=', '')
            ->select('external_reference')
            ->distinct()
            ->orderBy('external_reference')
            ->limit(100)
            ->pluck('external_reference')
            ->filter(fn($ref) => !preg_match($personCodePattern, $ref) && strlen($ref) < 50 && !empty(trim($ref)))
            ->values();

        // Group external refs by pattern (for better UX)
        $refGroups = [
            'Person Codes' => $personCodes,
            'BV Web Orders' => collect(['BV-WO-*']), // Wildcard for all BV web orders
            'BF Web Orders' => collect(['BF-WO-*']), // Wildcard for all BF web orders
            'BM Orders' => collect(['BM-*']), // Wildcard for all BM orders
            'Other' => $otherRefs->take(20)->sort()->values(),
        ];

        return view('users.create', [
            'employees' => $employees,
            'refGroups' => $refGroups,
        ]);
    }

    /**
     * Store a newly created user (admin only)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['boolean'],
            'role' => ['required', 'string', 'in:admin,manager,employee,external_ref,viewer'],
            'allowed_employees' => ['nullable', 'array'],
            'allowed_employees.*' => ['string'],
            'allowed_refs' => ['nullable', 'array'],
            'allowed_refs.*' => ['string'],
            'can_add_comments' => ['boolean'],
            'can_send_reminders' => ['boolean'],
            'can_sync' => ['boolean'],
        ]);

        // Checkboxes already come as arrays, just use them directly
        $allowedEmployees = !empty($validated['allowed_employees']) ? $validated['allowed_employees'] : null;
        $allowedRefs = !empty($validated['allowed_refs']) ? $validated['allowed_refs'] : null;

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->has('is_admin'),
            'role' => $validated['role'],
            'allowed_employees' => $allowedEmployees,
            'allowed_external_refs' => $allowedRefs,
            'can_add_comments' => $request->has('can_add_comments'),
            'can_send_reminders' => $request->has('can_send_reminders'),
            'can_sync' => $request->has('can_sync'),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user details (admin only)
     */
    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user (admin only)
     */
    public function edit(User $user): View
    {
        // Get unique employees from invoices (bypass user access scope for admin)
        $employees = \App\Models\Invoice::withoutGlobalScope('user_access')
            ->whereNotNull('employee_number')
            ->whereNotNull('employee_name')
            ->select('employee_number', 'employee_name')
            ->distinct()
            ->orderBy('employee_name')
            ->get()
            ->map(function($invoice) {
                return [
                    'number' => $invoice->employee_number,
                    'name' => $invoice->employee_name,
                ];
            })
            ->unique('number')
            ->values();

        // Person codes: 2-5 uppercase letters, optionally with numbers at the end (MW, LH, AKS, MB1, etc.)
        $personCodePattern = '/^[A-Z]{2,5}[0-9]{0,2}$/';

        // Fetch person codes separately (guaranteed inclusion of all 9 codes)
        $personCodes = \App\Models\Invoice::withoutGlobalScope('user_access')
            ->whereNotNull('external_reference')
            ->where('external_reference', '!=', '')
            ->select('external_reference')
            ->distinct()
            ->get()
            ->pluck('external_reference')
            ->filter(fn($ref) => preg_match($personCodePattern, $ref) && strlen($ref) < 50)
            ->sort()
            ->values();

        // Fetch other references (limited for performance)
        $otherRefs = \App\Models\Invoice::withoutGlobalScope('user_access')
            ->whereNotNull('external_reference')
            ->where('external_reference', '!=', '')
            ->select('external_reference')
            ->distinct()
            ->orderBy('external_reference')
            ->limit(100)
            ->pluck('external_reference')
            ->filter(fn($ref) => !preg_match($personCodePattern, $ref) && strlen($ref) < 50 && !empty(trim($ref)))
            ->values();

        // Group external refs by pattern (for better UX)
        $refGroups = [
            'Person Codes' => $personCodes,
            'BV Web Orders' => collect(['BV-WO-*']), // Wildcard for all BV web orders
            'BF Web Orders' => collect(['BF-WO-*']), // Wildcard for all BF web orders
            'BM Orders' => collect(['BM-*']), // Wildcard for all BM orders
            'Other' => $otherRefs->take(20)->sort()->values(),
        ];

        return view('users.edit', [
            'user' => $user,
            'employees' => $employees,
            'refGroups' => $refGroups,
        ]);
    }

    /**
     * Update the specified user (admin only)
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Prevent editing yourself (to avoid locking yourself out)
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot edit your own account. Ask another admin to make changes.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['boolean'],
            'role' => ['required', 'string', 'in:admin,manager,employee,external_ref,viewer'],
            'allowed_employees' => ['nullable', 'array'],
            'allowed_employees.*' => ['string'],
            'allowed_refs' => ['nullable', 'array'],
            'allowed_refs.*' => ['string'],
            'can_add_comments' => ['boolean'],
            'can_send_reminders' => ['boolean'],
            'can_sync' => ['boolean'],
        ]);

        // Checkboxes already come as arrays, just use them directly
        $allowedEmployees = !empty($validated['allowed_employees']) ? $validated['allowed_employees'] : null;
        $allowedRefs = !empty($validated['allowed_refs']) ? $validated['allowed_refs'] : null;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $request->has('is_admin'),
            'role' => $validated['role'],
            'allowed_employees' => $allowedEmployees,
            'allowed_external_refs' => $allowedRefs,
            'can_add_comments' => $request->has('can_add_comments'),
            'can_send_reminders' => $request->has('can_send_reminders'),
            'can_sync' => $request->has('can_sync'),
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Toggle user active status (block/unblock)
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent blocking yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot block your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'blocked';
        return redirect()->route('users.index')
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Remove the specified user (admin only)
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
