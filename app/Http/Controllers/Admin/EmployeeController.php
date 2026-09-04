<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::where('parent_id', panel_owner_id())
            ->where('role', 'employee')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $permissions = array_keys(config('permissions', []));

        return view('admin.employees.index', compact('employees', 'permissions'));
    }

    public function store(Request $request)
    {
        $permissions = array_keys(config('permissions', []));

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in($permissions),
        ]);

        User::create([
            'role' => 'employee',
            'parent_id' => panel_owner_id(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function update(Request $request, User $employee)
    {
        abort_unless($employee->role === 'employee' && (int) $employee->parent_id === (int) panel_owner_id(), 403);

        $permissions = array_keys(config('permissions', []));

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($employee->id)],
            'password' => 'nullable|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in($permissions),
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'permissions' => $request->permissions ?? [],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }
}


