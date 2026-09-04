<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::self()->latest('id')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'phone' => 'required|string|min:9|max:13|unique:customers,phone',
            'address' => 'nullable|string|max:500'
        ]);

        $input['admin_id'] = panel_owner_id();

        Customer::create($input);

        return redirect()->back()->with('success', 'Customer Added Successfully');
    }
}




