<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FloorController extends Controller
{
    public function index()
    {
        $floors = Floor::self()
            ->forActiveBranch()
            ->withCount('tables')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        $branches = seller_branches();

        return view('admin.floors.index', compact('floors', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('floors', 'name')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
            'priority' => 'nullable|integer|min:0|max:999',
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
        ]);

        Floor::create([
            'seller_id' => panel_owner_id(),
            'branch_id' => $data['branch_id'] ?? active_branch_id(),
            'name' => $data['name'],
            'priority' => $data['priority'] ?? 0,
        ]);

        return redirect()
            ->route('admin.floors.index')
            ->with('success', 'Floor created successfully.');
    }

    public function update(Request $request, Floor $floor)
    {
        abort_unless((int) $floor->seller_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('floors', 'name')
                    ->where(fn ($q) => $q->where('seller_id', panel_owner_id()))
                    ->ignore($floor->id),
            ],
            'priority' => 'nullable|integer|min:0|max:999',
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
        ]);

        $floor->update([
            'name' => $data['name'],
            'priority' => $data['priority'] ?? 0,
            'branch_id' => $data['branch_id'] ?? $floor->branch_id,
        ]);

        return redirect()
            ->route('admin.floors.index')
            ->with('success', 'Floor updated successfully.');
    }

    public function destroy(Floor $floor)
    {
        abort_unless((int) $floor->seller_id === (int) panel_owner_id(), 403);

        $floor->tables()->update(['floor_id' => null]);
        $floor->delete();

        return redirect()
            ->route('admin.floors.index')
            ->with('success', 'Floor deleted. Tables were unassigned.');
    }
}



