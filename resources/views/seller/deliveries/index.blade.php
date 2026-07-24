@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Delivery Orders Management</h1>
            <p class="text-xs text-slate-500">Track delivery orders, assign drivers, and manage courier dispatch progress</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-3">Order ID</th>
                        <th class="p-3">Customer Phone</th>
                        <th class="p-3">Address</th>
                        <th class="p-3">Driver</th>
                        <th class="p-3">Fee</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($deliveries as $d)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-bold text-brand-600">#{{ $d->sale?->order_id }}</td>
                            <td class="p-3 text-slate-700 font-medium">{{ $d->customer_phone }}</td>
                            <td class="p-3 text-slate-600 max-w-xs truncate">{{ $d->delivery_address }}</td>
                            <td class="p-3 text-slate-800 font-semibold">{{ $d->driver_name ? $d->driver_name . ' (' . $d->driver_phone . ')' : 'Unassigned' }}</td>
                            <td class="p-3 font-medium">৳{{ number_format($d->delivery_fee, 2) }}</td>
                            <td class="p-3">
                                @php
                                    $color = 'bg-amber-100 text-amber-800';
                                    if ($d->status == 'delivered') $color = 'bg-emerald-100 text-emerald-800';
                                    elseif ($d->status == 'out_for_delivery') $color = 'bg-blue-100 text-blue-800';
                                    elseif ($d->status == 'cancelled') $color = 'bg-rose-100 text-rose-800';
                                @endphp
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase {{ $color }}">{{ str_replace('_', ' ', $d->status) }}</span>
                            </td>
                            <td class="p-3 text-right">
                                <form action="{{ route('seller.deliveries.update-status', $d->id) }}" method="POST" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="text" name="driver_name" value="{{ $d->driver_name }}" placeholder="Driver name" class="form-control text-xs py-1 px-2 w-28">
                                    <input type="text" name="driver_phone" value="{{ $d->driver_phone }}" placeholder="Driver phone" class="form-control text-xs py-1 px-2 w-28">
                                    <select name="status" class="form-control text-xs py-1 px-1 w-32" onchange="this.form.submit()">
                                        <option value="pending" {{ $d->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="assigned" {{ $d->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="out_for_delivery" {{ $d->status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                        <option value="delivered" {{ $d->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $d->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $deliveries->links() }}</div>
    </div>
</div>
@endsection
