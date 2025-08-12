<?php

namespace App\Http\Controllers\SuperAdmin; // Ensure this namespace is correct

use App\Http\Controllers\Controller;   // Make sure to use the base Controller
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->paginate(10); // Get all users, paginated
        return view('superadmin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:waiter,reception,superadmin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user) // Route model binding
    {
        return view('superadmin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user) // Route model binding
    {
        return view('superadmin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user) // Route model binding
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'role' => ['required', 'in:waiter,reception,superadmin'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password is optional on update
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) // Route model binding
    {
        // Optional: Add logic here to prevent deleting the last superadmin or self
        if ($user->id === auth()->id() && $user->isSuperAdmin()) {
             return back()->with('error', 'You cannot delete your own super admin account.');
        }
        // Optional: Prevent deleting if it's the only super admin
        if ($user->isSuperAdmin() && User::where('role', 'superadmin')->count() <= 1) {
             return back()->with('error', 'Cannot delete the only super admin account.');
        }

        $user->delete();
        return redirect()->route('superadmin.users.index')
                         ->with('success', 'User deleted successfully.');
    }
}