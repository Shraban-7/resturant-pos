@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Gift Cards Management</h1>
            <p class="text-xs text-slate-500">Issue, track, and redeem digital gift cards at POS checkout</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Cards Table --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Issued Gift Cards</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-3">Code</th>
                            <th class="p-3">Recipient</th>
                            <th class="p-3">Initial Value</th>
                            <th class="p-3">Remaining Balance</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($giftCards as $card)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-brand-600">{{ $card->code }}</td>
                                <td class="p-3 text-slate-600">{{ $card->recipient_phone ?: ($card->recipient_email ?: 'General') }}</td>
                                <td class="p-3 font-medium">৳{{ number_format($card->initial_value, 2) }}</td>
                                <td class="p-3 font-bold text-emerald-600">৳{{ number_format($card->balance, 2) }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $card->is_active && $card->balance > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $card->is_active && $card->balance > 0 ? 'Active' : 'Depleted' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $giftCards->links() }}</div>
        </div>

        {{-- Issue Card Form --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm h-fit">
            <h2 class="text-sm font-semibold text-slate-800 mb-3">Issue New Gift Card</h2>
            <form action="{{ route('seller.gift-cards.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label">Initial Amount (৳)</label>
                    <input type="number" name="initial_value" step="0.01" min="1" class="form-control" placeholder="e.g. 1000" required>
                </div>
                <div>
                    <label class="form-label">Recipient Phone (Optional)</label>
                    <input type="text" name="recipient_phone" class="form-control" placeholder="017xxxxxxxx">
                </div>
                <div>
                    <label class="form-label">Recipient Email (Optional)</label>
                    <input type="email" name="recipient_email" class="form-control" placeholder="customer@example.com">
                </div>
                <div>
                    <label class="form-label">Expiry Date (Optional)</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-full">Issue Gift Card</button>
            </form>
        </div>
    </div>
</div>
@endsection
