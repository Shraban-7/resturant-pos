<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::self()
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $groups = config('rbac.groups', []);
        $flat = array_keys(config('permissions', []));
        if (empty($groups) && ! empty($flat)) {
            $groups = ['Permissions' => array_combine($flat, $flat)];
        }

        $defaults = Role::defaultTemplates();

        return view('admin.roles.index', compact('roles', 'groups', 'defaults'));
    }

    public function store(Request $request)
    {
        $validPermissions = array_keys(config('permissions', []));

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in($validPermissions),
            'is_active' => 'nullable|boolean',
        ]);

        Role::create([
            'admin_id' => panel_owner_id(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'permissions' => $data['permissions'] ?? [],
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role saved.');
    }

    public function update(Request $request, Role $role)
    {
        abort_unless((int) $role->admin_id === (int) panel_owner_id(), 403);

        $validPermissions = array_keys(config('permissions', []));

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('admin_id', panel_owner_id()))
                    ->ignore($role->id),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in($validPermissions),
            'is_active' => 'nullable|boolean',
        ]);

        $role->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'permissions' => $data['permissions'] ?? [],
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $role->is_active,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        abort_unless((int) $role->admin_id === (int) panel_owner_id(), 403);

        if ($role->is_system) {
            return redirect()->route('admin.roles.index')->with('error', 'System role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role is assigned to employees. Reassign them first.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    public function toggleStatus(Role $role)
    {
        abort_unless((int) $role->admin_id === (int) panel_owner_id(), 403);

        $role->update(['is_active' => ! $role->is_active]);

        return redirect()->route('admin.roles.index')->with('success', 'Role status updated.');
    }

    public function seedDefaults()
    {
        $ownerId = panel_owner_id();

        foreach (Role::defaultTemplates() as $name => $permissions) {
            Role::firstOrCreate(
                ['admin_id' => $ownerId, 'slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'permissions' => $permissions,
                    'is_active' => true,
                ]
            );
        }

        return redirect()->route('admin.roles.index')->with('success', 'Default roles restored.');
    }
}
