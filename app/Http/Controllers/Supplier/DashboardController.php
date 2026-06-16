<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Supplier;
use App\Models\Customer;
use App\Models\SupplierProduct;
use App\Models\SupplierSale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');

        $salesQuery = SupplierSale::self();
        if ($fromDate && $toDate) {
            $salesQuery = $salesQuery->filterByDate($fromDate, $toDate);
        }

        $sales = $salesQuery->get();

        $totalProducts = SupplierProduct::self()->count();
        $totalOrders = $sales->count();
        $totalSales = $sales->sum('payable');
        $totalCustomers = $salesQuery->select('supplier_id', 'customer_id')
            ->distinct('customer_id')
            ->count();
        $recentOrders = $sales->sortByDesc('id')->take(5);
        $popularItems = $this->popularItems();
        $dailySales = $this->dailySales($fromDate, $toDate);
        $totalRevenue = $this->totalRevenue($sales);


        $cashInHand = $sales->sum('paid');
        $due = $sales->sum('due');

        return view('supplier.dashboard', compact('totalProducts', 'totalOrders', 'totalSales', 'totalCustomers', 'recentOrders', 'popularItems', 'dailySales', 'totalRevenue', 'cashInHand', 'due'));
    }

    private function totalRevenue($sales)
    {
        $sales->load('items');
        $totalRevenue = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $profitPerUnit = $item->unit_price - $item->buying_price;

                if ($profitPerUnit > 0) {
                    $totalRevenue += $profitPerUnit * $item->quantity;
                }
            }
        }

        return $totalRevenue;
    }

    private function dailySales($fromDate = null, $toDate = null)
    {

        $sales = SupplierSale::query()->self();

        if (!is_null($fromDate) && !is_null($toDate)) {
            $sales->filterByDate($fromDate, $toDate);
        } else {
            $sales->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $sales = $sales->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as sale_count')
        )->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $sales;
    }

    private function popularItems()
    {
        $popularItems = DB::table('supplier_sale_items')
            ->select('item_id', DB::raw('COUNT(*) as sale_count'))
            ->groupBy('item_id')
            ->orderByDesc('sale_count')
            ->limit(8)
            ->get();

        $productNames  = SupplierProduct::select('id', 'name')->whereIn('id', $popularItems->pluck('item_id')->toArray())->get();
        $productNames = collect($productNames);

        foreach ($popularItems as $item) {
            $item->name = $productNames->where('id', $item->item_id)->first()->name;
        }

        $totalSaleCount = $popularItems->sum('sale_count');

        return $popularItems->map(function ($popularItem) use ($totalSaleCount) {
            return [
                'name' => $popularItem->name,
                'sale_count' => $popularItem->sale_count,
                'percentage' => round(($popularItem->sale_count / $totalSaleCount) * 100)
            ];
        });
    }
}
