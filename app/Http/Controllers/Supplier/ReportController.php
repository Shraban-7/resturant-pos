<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use App\Models\SupplierSale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');

        $productQuery = SupplierProduct::self();
        if ($fromDate && $toDate) {
            $productQuery = $productQuery->filterByDate($fromDate, $toDate);
        }
        $products = $productQuery->get();
        $totalPurchase = $products->sum('buying_price');

        $salesQuery = SupplierSale::self();
        if ($fromDate && $toDate) {
            $salesQuery = $salesQuery->filterByDate($fromDate, $toDate);
        }
        $sales = $salesQuery->get();

        $totalSales = $sales->sum('payable');
        $cashInHand = $sales->sum('paid');
        $due = $sales->sum('due');

        $profitLoss = $this->calculateProfitLoss($sales);
        $profit = $profitLoss['profit'];
        $loss = $profitLoss['loss'];

        $netProfit = $profit - $loss;

        $profitPercentage = $totalSales > 0 ? ($profit / $totalSales) * 100 : 0;
        $lossPercentage = $totalSales > 0 ? ($loss / $totalSales) * 100 : 0;
        $netProfitPercentage = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;


        return view('supplier.report.index', compact('totalPurchase', 'totalSales', 'cashInHand', 'due', 'profit', 'loss', 'netProfit', 'profitPercentage', 'lossPercentage', 'netProfitPercentage'));
    }


    private function calculateProfitLoss($sales)
    {
        $sales->load('items');
        $totalProfit = 0;
        $totalLoss = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $profitPerUnit = $item->unit_price - $item->buying_price;

                if ($profitPerUnit > 0) {
                    $totalProfit += $profitPerUnit * $item->quantity;
                } elseif ($profitPerUnit < 0) {
                    $totalLoss += abs($profitPerUnit) * $item->quantity;
                }
            }
        }

        return [
            'profit' => $totalProfit,
            'loss' => $totalLoss
        ];
    }
}
