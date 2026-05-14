<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = SellerEmployee::where('seller_id', Auth::id())->latest('id')->paginate(20)->withQueryString();
        $roles = SellerEmployee::roles();


        return view('seller.employees.index', compact('employees', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:seller_employees,name',
        ]);

        SellerEmployee::create([
            'seller_id' => Auth::id(),
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->route('seller.employees.index')->with('success', 'Employee created successfully.');
    }

    public function update(Request $request, SellerEmployee $employee)
    {
        $request->validate([
            'name' => 'required|unique:seller_employees,name,' . $employee->id,
        ]);

        $employee->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->route('seller.employees.index')->with('success', 'Employee updated successfully.');
    }
}
