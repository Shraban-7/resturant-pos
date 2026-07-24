<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = SellerEmployee::self()
            ->forActiveBranch()
            ->with('branch')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $roles = SellerEmployee::roles();
        $branches = seller_branches();

        return view('seller.employees.index', compact('employees', 'roles', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:seller_employees,name',
            'role' => 'nullable|in:'.implode(',', SellerEmployee::roles()),
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('seller_id', Auth::id())),
            ],
        ]);

        SellerEmployee::create([
            'seller_id' => Auth::id(),
            'branch_id' => $request->branch_id ?: active_branch_id(),
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->route('seller.employees.index')->with('success', 'Employee created successfully.');
    }

    public function update(Request $request, SellerEmployee $employee)
    {
        abort_unless((int) $employee->seller_id === (int) Auth::id(), 403);

        $request->validate([
            'name' => 'required|unique:seller_employees,name,'.$employee->id,
            'role' => 'nullable|in:'.implode(',', SellerEmployee::roles()),
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('seller_id', Auth::id())),
            ],
        ]);

        $employee->update([
            'name' => $request->name,
            'role' => $request->role,
            'branch_id' => $request->branch_id ?: $employee->branch_id,
        ]);

        return redirect()->route('seller.employees.index')->with('success', 'Employee updated successfully.');
    }
}
