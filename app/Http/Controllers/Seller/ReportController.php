<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->get('fromDate') ?: date('Y-m-d');
        $toDate = $request->get('toDate') ?: date('Y-m-d');
        $branchFilter = $request->get('branch_id');

        $productQuery = Product::self();
        if ($fromDate && $toDate) {
            $productQuery = $productQuery->filterByDate($fromDate, $toDate);
        }
        $products = $productQuery->get();
        $totalPurchase = $products->sum('buying_price');

        $salesQuery = Sale::self()->with('branch');
        if ($fromDate && $toDate) {
            $salesQuery = $salesQuery->filterByDate($fromDate, $toDate);
        }

        if ($branchFilter === 'unassigned') {
            $salesQuery->whereNull('branch_id');
        } elseif ($branchFilter) {
            $salesQuery->where('branch_id', (int) $branchFilter);
        }

        $sales = $salesQuery->get();

        $totalSales = $sales->sum('payable');
        $cashInHand = $sales->sum('paid');
        $due = $sales->sum('due');
        $profit = $this->totalProfit($sales);

        $branches = Branch::self()->orderByDesc('is_default')->orderBy('name')->get();

        $branchComparison = $branches->map(function (Branch $branch) use ($fromDate, $toDate) {
            $branchSales = Sale::self()
                ->where('branch_id', $branch->id)
                ->when($fromDate && $toDate, fn ($q) => $q->filterByDate($fromDate, $toDate))
                ->with('items')
                ->get();

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'orders' => $branchSales->count(),
                'sales' => $branchSales->sum('payable'),
                'paid' => $branchSales->sum('paid'),
                'due' => $branchSales->sum('due'),
                'profit' => $this->totalProfit($branchSales),
            ];
        });

        $unassignedSales = Sale::self()
            ->whereNull('branch_id')
            ->when($fromDate && $toDate, fn ($q) => $q->filterByDate($fromDate, $toDate))
            ->with('items')
            ->get();

        if ($unassignedSales->isNotEmpty() || $branches->isNotEmpty()) {
            $branchComparison = $branchComparison->push([
                'id' => null,
                'name' => 'Unassigned / HQ',
                'code' => null,
                'orders' => $unassignedSales->count(),
                'sales' => $unassignedSales->sum('payable'),
                'paid' => $unassignedSales->sum('paid'),
                'due' => $unassignedSales->sum('due'),
                'profit' => $this->totalProfit($unassignedSales),
            ]);
        }

        return view('seller.report.index', compact(
            'totalPurchase',
            'totalSales',
            'cashInHand',
            'due',
            'profit',
            'branches',
            'branchComparison',
            'fromDate',
            'toDate',
            'branchFilter'
        ));
    }

    private function totalProfit($sales)
    {
        $sales->loadMissing('items');
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
