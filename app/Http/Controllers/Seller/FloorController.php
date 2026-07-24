<?php

namespace App\Http\Controllers\Seller;

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
            ->withCount('tables')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        return view('seller.floors.index', compact('floors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('floors', 'name')->where(fn ($q) => $q->where('seller_id', Auth::id())),
            ],
            'priority' => 'nullable|integer|min:0|max:999',
        ]);

        Floor::create([
            'seller_id' => Auth::id(),
            'name' => $data['name'],
            'priority' => $data['priority'] ?? 0,
        ]);

        return redirect()
            ->route('seller.floors.index')
            ->with('success', 'Floor created successfully.');
    }

    public function update(Request $request, Floor $floor)
    {
        abort_unless((int) $floor->seller_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('floors', 'name')
                    ->where(fn ($q) => $q->where('seller_id', Auth::id()))
                    ->ignore($floor->id),
            ],
            'priority' => 'nullable|integer|min:0|max:999',
        ]);

        $floor->update([
            'name' => $data['name'],
            'priority' => $data['priority'] ?? 0,
        ]);

        return redirect()
            ->route('seller.floors.index')
            ->with('success', 'Floor updated successfully.');
    }

    public function destroy(Floor $floor)
    {
        abort_unless((int) $floor->seller_id === (int) Auth::id(), 403);

        $floor->tables()->update(['floor_id' => null]);
        $floor->delete();

        return redirect()
            ->route('seller.floors.index')
            ->with('success', 'Floor deleted. Tables were unassigned.');
    }
}
