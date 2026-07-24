<?php

namespace App\Http\Controllers\Seller;

use App\Events\TableStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DiningTableController extends Controller
{
    public function index()
    {
        $tables = DiningTable::where('seller_id', Auth::id())->get();
        $tables->each(fn (DiningTable $table) => $table->ensureQrToken());
        $tableStatus = DiningTable::statuses();

        return view('seller.dining-tables.index', compact('tables', 'tableStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:dining_tables,name',
        ]);

        DiningTable::create([
            'seller_id' => Auth::id(),
            'name' => $request->name,
            'status' => DiningTable::FREE,
            'qr_code_token' => \Illuminate\Support\Str::random(48),
        ]);

        return redirect()->back()->with('success', 'Table created successfully.');
    }

    public function update(Request $request, DiningTable $table)
    {
        abort_unless((int) $table->seller_id === (int) Auth::id(), 403);

        $request->validate([
            'name' => 'required|unique:dining_tables,name,' . $table->id,
        ]);

        $table->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        event(new TableStatusChangedEvent($table->fresh()));

        return redirect()->back()->with('success', 'Table updated successfully.');
    }

    public function destroy(DiningTable $table)
    {
        abort_unless((int) $table->seller_id === (int) Auth::id(), 403);

        $table->delete();

        return redirect()->route('seller.diningTables.index')->with('success', 'Table deleted successfully.');
    }

    public function qrCard(DiningTable $table)
    {
        abort_unless((int) $table->seller_id === (int) Auth::id(), 403);

        $token = $table->ensureQrToken();
        $menuUrl = route('menu.index', $table);
        $trackerUrl = route('menu.tracker', $token);
        $qrSvg = QrCode::size(280)->margin(1)->generate($menuUrl);

        return view('seller.dining-tables.qr-card', compact('table', 'menuUrl', 'trackerUrl', 'qrSvg'));
    }

    public function qrSvg(DiningTable $table)
    {
        abort_unless((int) $table->seller_id === (int) Auth::id(), 403);

        $table->ensureQrToken();
        $svg = QrCode::size(400)->margin(1)->generate(route('menu.index', $table));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="table-' . $table->id . '-qr.svg"',
        ]);
    }
}
