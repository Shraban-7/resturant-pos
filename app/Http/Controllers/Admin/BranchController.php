<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Floor;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::self()
            ->withCount(['tables', 'employees', 'floors'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('branches', 'name')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'code' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data) {
            $isDefault = (bool) ($data['is_default'] ?? false)
                || ! Branch::self()->exists();

            if ($isDefault) {
                Branch::self()->update(['is_default' => false]);
            }

            $branch = Branch::create([
                'admin_id' => panel_owner_id(),
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
                'is_default' => $isDefault,
            ]);

            if ($isDefault || ! session('active_branch_id')) {
                session(['active_branch_id' => $branch->id]);
            }
        });

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        abort_unless((int) $branch->admin_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('branches', 'name')
                    ->where(fn ($q) => $q->where('admin_id', panel_owner_id()))
                    ->ignore($branch->id),
            ],
            'code' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data, $branch) {
            $isDefault = (bool) ($data['is_default'] ?? false);
            if ($isDefault) {
                Branch::self()->whereKeyNot($branch->id)->update(['is_default' => false]);
            }

            $branch->update([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $branch->is_active,
                'is_default' => $isDefault || $branch->is_default,
            ]);
        });

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        abort_unless((int) $branch->admin_id === (int) panel_owner_id(), 403);

        DB::transaction(function () use ($branch) {
            DiningTable::self()->where('branch_id', $branch->id)->update(['branch_id' => null]);
            Floor::self()->where('branch_id', $branch->id)->update(['branch_id' => null]);
            Employee::self()->where('branch_id', $branch->id)->update(['branch_id' => null]);
            Reservation::self()->where('branch_id', $branch->id)->update(['branch_id' => null]);
            // Keep historical sales.branch_id for reporting; do not null them.

            $wasDefault = $branch->is_default;
            $branchId = $branch->id;
            $branch->delete();

            if ((int) session('active_branch_id') === (int) $branchId) {
                session()->forget('active_branch_id');
            }

            if ($wasDefault) {
                $next = Branch::self()->active()->orderBy('name')->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                    session(['active_branch_id' => $next->id]);
                }
            }
        });

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch deleted. Linked floors/tables/staff were unassigned.');
    }

    public function switch(Request $request)
    {
        $data = $request->validate([
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q
                    ->where('admin_id', panel_owner_id())
                    ->where('is_active', true)),
            ],
        ]);

        if (empty($data['branch_id'])) {
            session(['active_branch_id' => 'all']);
        } else {
            session(['active_branch_id' => (int) $data['branch_id']]);
        }

        return back()->with('success', 'Active branch updated.');
    }
}




