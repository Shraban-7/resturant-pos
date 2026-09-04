<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;

use App\Events\TableStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DiningTableController extends Controller
{
    public function index()
    {
        $tables = DiningTable::self()->forActiveBranch()->with(['floor', 'branch'])->orderBy('name')->get();
        $tables->each(fn (DiningTable $table) => $table->ensureQrToken());
        $tableStatus = DiningTable::statuses();
        $floors = Floor::self()->forActiveBranch()->orderBy('priority')->orderBy('name')->get();
        $branches = admin_branches();

        return view('admin.dining-tables.index', compact('tables', 'tableStatus', 'floors', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dining_tables', 'name')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'floor_id' => [
                'nullable',
                Rule::exists('floors', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
        ]);

        DiningTable::create([
            'admin_id' => panel_owner_id(),
            'branch_id' => $data['branch_id'] ?? active_branch_id(),
            'name' => $data['name'],
            'floor_id' => $data['floor_id'] ?? null,
            'status' => TableStatus::FREE,
            'qr_code_token' => Str::random(48),
        ]);

        return redirect()->back()->with('success', 'Table created successfully.');
    }

    public function update(Request $request, DiningTable $table)
    {
        abort_unless((int) $table->admin_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dining_tables', 'name')
                    ->where(fn ($q) => $q->where('admin_id', panel_owner_id()))
                    ->ignore($table->id),
            ],
            'status' => 'required|in:'.implode(',', DiningTable::statuses()),
            'floor_id' => [
                'nullable',
                Rule::exists('floors', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
        ]);

        $table->update([
            'name' => $data['name'],
            'status' => $data['status'],
            'floor_id' => $data['floor_id'] ?? null,
            'branch_id' => array_key_exists('branch_id', $data) ? $data['branch_id'] : $table->branch_id,
        ]);

        event(new TableStatusChangedEvent($table->fresh()));

        return redirect()->back()->with('success', 'Table updated successfully.');
    }

    public function destroy(DiningTable $table)
    {
        abort_unless((int) $table->admin_id === (int) panel_owner_id(), 403);

        $table->delete();

        return redirect()->route('admin.diningTables.index')->with('success', 'Table deleted successfully.');
    }

    public function floorMap(Request $request)
    {
        $floors = Floor::self()->forActiveBranch()->orderBy('priority')->orderBy('name')->get();

        // Explicit floor_id=0 (or empty) means Unassigned; missing param defaults to first floor.
        if ($request->has('floor_id')) {
            $floorId = $request->integer('floor_id') ?: null;
        } else {
            $floorId = $floors->first()?->id;
        }

        $tables = DiningTable::self()
            ->forActiveBranch()
            ->when($floorId !== null, fn ($q) => $q->where('floor_id', $floorId))
            ->when($floorId === null, fn ($q) => $q->whereNull('floor_id'))
            ->orderBy('name')
            ->get();

        return view('admin.dining-tables.floor-map', compact('floors', 'tables', 'floorId'));
    }

    public function savePositions(Request $request)
    {
        $data = $request->validate([
            'positions' => 'required|array|min:1',
            'positions.*.id' => [
                'required',
                'integer',
                Rule::exists('dining_tables', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'positions.*.x' => 'required|integer|min:0|max:5000',
            'positions.*.y' => 'required|integer|min:0|max:5000',
        ]);

        foreach ($data['positions'] as $position) {
            DiningTable::self()
                ->whereKey($position['id'])
                ->update([
                    'x_position' => $position['x'],
                    'y_position' => $position['y'],
                ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Floor plan saved.',
        ]);
    }

    public function qrCard(DiningTable $table)
    {
        abort_unless((int) $table->admin_id === (int) panel_owner_id(), 403);

        $token = $table->ensureQrToken();
        $menuUrl = route('menu.index', $table);
        $trackerUrl = route('menu.tracker', $token);
        $qrSvg = QrCode::size(280)->margin(1)->generate($menuUrl);

        return view('admin.dining-tables.qr-card', compact('table', 'menuUrl', 'trackerUrl', 'qrSvg'));
    }

    public function qrSvg(DiningTable $table)
    {
        abort_unless((int) $table->admin_id === (int) panel_owner_id(), 403);

        $table->ensureQrToken();
        $svg = QrCode::size(400)->margin(1)->generate(route('menu.index', $table));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="table-'.$table->id.'-qr.svg"',
        ]);
    }
}








