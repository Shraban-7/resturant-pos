<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');

        $productQuery = Product::self();
        if ($fromDate && $toDate) {
            $productQuery = $productQuery->filterByDate($fromDate, $toDate);
        }
        $products = $productQuery->get();
        $totalPurchase = $products->sum('buying_price');

        $salesQuery = Sale::self();
        if ($fromDate && $toDate) {
            $salesQuery = $salesQuery->filterByDate($fromDate, $toDate);
        }
        $sales = $salesQuery->get();

        $totalSales = $sales->sum('payable');
        $cashInHand = $sales->sum('paid');
        $due = $sales->sum('due');

        $profit = $this->totalProfit($sales);

        return view('seller.report.index', compact('totalPurchase', 'totalSales', 'cashInHand', 'due', 'profit'));
    }


    private function totalProfit($sales)
    {
        $sales->load('items');
        $totalProfit = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $profitPerUnit = $item->unit_price - $item->buying_price;

                if ($profitPerUnit > 0) {
                    $totalProfit += $profitPerUnit * $item->quantity;
                }
            }
        }

        return $totalProfit;
    }
}
