<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiningTableController extends Controller
{
    public function index()
    {
        $tables = DiningTable::where('seller_id', Auth::id())->get();
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
        ]);

        return redirect()->back()->with('success', 'Table created successfully.');
    }

    public function update(Request $request, DiningTable $table)
    {
        $request->validate([
            'name' => 'required|unique:dining_tables,name,' . $table->id,
        ]);

        $table->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Table updated successfully.');
    }

    public function destroy(DiningTable $table)
    {
        $table->delete();

        return redirect()->route('seller.dining-tables.index')->with('success', 'Table deleted successfully.');
    }
}
