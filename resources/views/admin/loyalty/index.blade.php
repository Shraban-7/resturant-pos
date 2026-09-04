@extends('layouts.admin')

@section('title', 'Loyalty Program')
@section('page_title', 'Loyalty Program')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Loyalty Program</h1>
            <p class="text-xs text-slate-500">Manage customer reward points, tier progression, and manual point adjustments</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Customer Loyalty Balances --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Customer Balances</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Points Balance</th>
                            <th class="p-3">Tier</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($customers as $c)
                            @php
                                $tier = 'Bronze';
                                if ($c->loyalty_points_balance >= 1000) $tier = 'Gold';
                                elseif ($c->loyalty_points_balance >= 500) $tier = 'Silver';
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-semibold text-slate-800">{{ $c->name }}</td>
                                <td class="p-3 text-slate-600">{{ $c->phone }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded bg-brand-50 text-brand-700 font-bold">{{ $c->loyalty_points_balance }} pts</span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tier == 'Gold' ? 'bg-amber-100 text-amber-800' : ($tier == 'Silver' ? 'bg-slate-200 text-slate-700' : 'bg-orange-100 text-orange-800') }}">{{ $tier }}</span>
                                </td>
                                <td class="p-3 text-right">
                                    <button class="btn btn-ghost btn-sm text-brand-600" onclick="openAdjustModal({{ $c->id }}, '{{ $c->name }}')">Adjust</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $customers->links() }}</div>
        </div>

        {{-- Adjust Form & Recent Activity --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-800 mb-3">Adjust Points</h2>
                <form action="{{ route('admin.loyalty.adjust') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="customer_id" id="adjustCustomerId">
                    <div>
                        <label class="form-label">Selected Customer</label>
                        <input type="text" id="adjustCustomerName" class="form-control bg-slate-50" readonly placeholder="Click adjust on a customer">
                    </div>
                    <div>
                        <label class="form-label">Points (+ to add, - to deduct)</label>
                        <input type="number" name="points" class="form-control" placeholder="e.g. 50 or -20" required>
                    </div>
                    <div>
                        <label class="form-label">Reason</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Birthday Bonus">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Save Adjustment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openAdjustModal(id, name) {
        document.getElementById('adjustCustomerId').value = id;
        document.getElementById('adjustCustomerName').value = name;
    }
</script>
@endsection

