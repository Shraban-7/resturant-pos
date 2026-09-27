<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = panel_owner_id();

        $query = User::with(['roleModel', 'branch'])
            ->where('parent_id', $ownerId)
            ->where('role', UserRole::EMPLOYEE);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            if ($request->query('role_id') === 'none') {
                $query->whereNull('role_id');
            } else {
                $query->where('role_id', $request->query('role_id'));
            }
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $employees = $query->latest('id')->paginate(20)->withQueryString();

        $roles = Role::self()->active()->orderBy('name')->get();
        $branches = Branch::self()->active()->orderBy('name')->get();
        $groups = config('rbac.groups', []);
        $permissions = array_keys(config('permissions', []));
        $jobTitles = config('rbac.job_titles', ['Manager', 'Cashier', 'Waiter', 'Chef', 'Cleaner']);

        return view('admin.employees.index', compact(
            'employees', 'roles', 'branches', 'groups', 'permissions', 'jobTitles'
        ));
    }

    protected function employeeRules(User $employee = null): array
    {
        $permissions = array_keys(config('permissions', []));
        $ownerId = panel_owner_id();

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email',
                $employee
                    ? Rule::unique('users', 'email')->ignore($employee->id)
                    : 'unique:users,email',
            ],
            'phone' => 'nullable|string|max:20',
            'password' => [$employee ? 'nullable' : 'required', 'min:8'],
            'role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(fn ($q) => $q->where('admin_id', $ownerId)),
            ],
            'job_title' => 'nullable|string|max:100',
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('admin_id', $ownerId)),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in($permissions),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->employeeRules());

        User::create([
            'role' => UserRole::EMPLOYEE,
            'parent_id' => panel_owner_id(),
            'role_id' => $data['role_id'] ?? null,
            'status' => $data['status'] ?? 'active',
            'job_title' => $data['job_title'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee saved.');
    }

    public function update(Request $request, User $employee)
    {
        $this->authorizeEmployee($employee);

        $data = $request->validate($this->employeeRules($employee));

        $update = [
            'role_id' => $data['role_id'] ?? null,
            'status' => $data['status'] ?? $employee->status ?? 'active',
            'job_title' => $data['job_title'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'permissions' => $data['permissions'] ?? [],
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $employee->update($update);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(User $employee)
    {
        $this->authorizeEmployee($employee);

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted.');
    }

    public function toggleStatus(User $employee)
    {
        $this->authorizeEmployee($employee);

        $employee->update([
            'status' => ($employee->status ?? 'active') === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee status updated.');
    }

    protected function authorizeEmployee(User $employee): void
    {
        abort_unless(
            $employee->role === UserRole::EMPLOYEE
            && (int) $employee->parent_id === (int) panel_owner_id(),
            403
        );
    }
}
