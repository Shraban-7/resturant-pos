<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierSale;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customerIds = SupplierSale::self()
            ->select('customer_id')
            ->distinct()
            ->pluck('customer_id');

        $customers = User::whereIn('id', $customerIds)->latest('id')->paginate(20)->withQueryString();

        return view('supplier.customers.index', compact('customers'));
    }
}
