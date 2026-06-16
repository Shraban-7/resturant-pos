@extends('layouts.admin')
@section('title', 'Customers')
@section('page_title', 'Customers')
@section('breadcrumb')
<a href="{{ route('supplier.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Customers</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $customers->total() }} {{ Str::plural('customer', $customers->total()) }} in your book</p>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Customer Since</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="text-slate-500">#{{ $customer->id }}</td>
                        <td class="font-medium text-slate-800">{{ $customer->name }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td class="text-slate-600">{{ $customer->address }}</td>
                        <td class="text-slate-500">{{ $customer->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <i class="ri-team-line"></i>
                            <h3>No customers yet</h3>
                            <p>Customers will appear here once recorded.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div class="card-footer">
            {{ $customers->links() }}
        </div>
    @endif
</div>

@endsection
