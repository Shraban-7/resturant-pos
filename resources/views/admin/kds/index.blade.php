@extends('layouts.admin')

@section('title', 'Kitchen Display')

@section('full_page')
<div
    class="min-h-[calc(100vh-2rem)] -m-4 sm:-m-6"
    x-data="kdsApp(@js($tickets->map(fn ($t) => [
        'ticket_id' => $t->id,
        'ticket_number' => $t->ticket_number,
        'status' => $t->status,
        'sale_id' => $t->sale_id,
        'order_id' => $t->sale?->order_id,
        'table_id' => $t->dining_table_id,
        'table_name' => $t->diningTable?->name,
        'waiter_name' => $t->sale?->waiter?->name,
        'order_type' => $t->sale?->order_type ?? 'dine_in',
        'items' => $t->items->map(fn ($i) => [
            'id' => $i->id,
            'product_id' => $i->product_id,
            'name' => $i->product_name,
            'quantity' => (float) $i->quantity,
            'modifiers' => collect($i->modifiers_json ?? [])->pluck('name')->filter()->values()->all(),
            'special_instructions' => $i->special_instructions,
            'status' => $i->status,
        ])->values()->all(),
        'created_at' => optional($t->created_at)?->toIso8601String(),
        'fired_at' => optional($t->fired_at ?? $t->created_at)?->toIso8601String(),
    ])->values()), {{ (int) $sellerId }})"
    x-cloak
>
    <div class="sticky top-0 z-20 bg-slate-900 text-white px-4 py-3 flex flex-wrap items-center justify-between gap-3 shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10 hover:bg-white/20">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <div class="text-lg font-semibold leading-tight">Kitchen Display</div>
                <div class="text-xs text-slate-300 flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full" :class="connected ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                    <span x-text="connected ? 'Live' : 'Connecting…'"></span>
                    <span class="text-slate-500">·</span>
                    <span x-text="tickets.length + ' active'"></span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="btn btn-sm bg-white/10 text-white hover:bg-white/20 border-0" @click="soundEnabled = !soundEnabled">
                <i class="ri-volume-up-line" x-show="soundEnabled"></i>
                <i class="ri-volume-mute-line" x-show="!soundEnabled"></i>
                <span x-text="soundEnabled ? 'Sound On' : 'Sound Off'"></span>
            </button>
            <button type="button" class="btn btn-sm bg-white/10 text-white hover:bg-white/20 border-0" @click="location.reload()">
                <i class="ri-refresh-line"></i> Refresh
            </button>
        </div>
    </div>

    <div class="p-4 bg-slate-100 min-h-[calc(100vh-4.5rem)]">
        <template x-if="tickets.length === 0">
            <div class="flex flex-col items-center justify-center py-24 text-slate-500">
                <i class="ri-restaurant-2-line text-5xl mb-3 opacity-40"></i>
                <p class="text-lg font-medium text-slate-700">No active kitchen tickets</p>
                <p class="text-sm">New orders will appear here instantly.</p>
            </div>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
            <template x-for="ticket in sortedTickets" :key="ticket.ticket_id">
                <article
                    class="rounded-xl border-2 bg-white shadow-sm overflow-hidden flex flex-col transition"
                    :class="urgencyBorder(ticket)"
                >
                    <header class="px-4 py-3 border-b border-slate-100 flex items-start justify-between gap-2"
                            :class="urgencyHeader(ticket)">
                        <div>
                            <div class="font-bold text-lg leading-tight" x-text="ticket.table_name || 'No table'"></div>
                            <div class="text-xs opacity-80 mt-0.5">
                                <span x-text="ticket.ticket_number"></span>
                                <template x-if="ticket.order_id">
                                    <span> · <span x-text="ticket.order_id"></span></span>
                                </template>
                            </div>
                            <div class="text-xs opacity-70 mt-0.5" x-show="ticket.waiter_name">
                                Waiter: <span x-text="ticket.waiter_name"></span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-2xl font-bold tabular-nums" x-text="elapsedLabel(ticket)"></div>
                            <div class="text-[10px] uppercase tracking-wider opacity-70" x-text="ticket.status"></div>
                        </div>
                    </header>

                    <ul class="flex-1 px-4 py-3 space-y-2 text-sm">
                        <template x-for="item in ticket.items" :key="item.id">
                            <li class="border-b border-slate-50 pb-2 last:border-0">
                                <div class="flex gap-2 font-semibold text-slate-900">
                                    <span class="text-brand-600 tabular-nums" x-text="item.quantity + '×'"></span>
                                    <span x-text="item.name"></span>
                                </div>
                                <template x-if="item.modifiers && item.modifiers.length">
                                    <div class="text-xs text-slate-500 mt-0.5 pl-6" x-text="item.modifiers.join(', ')"></div>
                                </template>
                                <template x-if="item.special_instructions">
                                    <div class="text-xs text-amber-700 mt-0.5 pl-6 italic" x-text="item.special_instructions"></div>
                                </template>
                            </li>
                        </template>
                    </ul>

                    <footer class="p-3 bg-slate-50 border-t border-slate-100 grid grid-cols-1 gap-2">
                        <template x-if="ticket.status === 'pending'">
                            <button type="button"
                                    class="btn btn-primary w-full py-3 text-base"
                                    :disabled="busyId === ticket.ticket_id"
                                    @click="setStatus(ticket, 'preparing')">
                                Start Prep
                            </button>
                        </template>
                        <template x-if="ticket.status === 'preparing'">
                            <button type="button"
                                    class="btn btn-success w-full py-3 text-base"
                                    :disabled="busyId === ticket.ticket_id"
                                    @click="setStatus(ticket, 'ready')">
                                Mark Ready
                            </button>
                        </template>
                        <template x-if="ticket.status === 'ready'">
                            <button type="button"
                                    class="btn w-full py-3 text-base bg-slate-800 text-white hover:bg-slate-900"
                                    :disabled="busyId === ticket.ticket_id"
                                    @click="setStatus(ticket, 'served')">
                                Serve / Clear
                            </button>
                        </template>
                    </footer>
                </article>
            </template>
        </div>
    </div>
</div>
@endsection

@push('footer')
<script>
function kdsApp(initialTickets, sellerId) {
    return {
        tickets: initialTickets || [],
        sellerId,
        connected: false,
        soundEnabled: true,
        busyId: null,
        now: Date.now(),
        _timer: null,

        get sortedTickets() {
            return [...this.tickets].sort((a, b) => {
                const ta = new Date(a.fired_at || a.created_at).getTime();
                const tb = new Date(b.fired_at || b.created_at).getTime();
                return ta - tb;
            });
        },

        init() {
            this._timer = setInterval(() => { this.now = Date.now(); }, 1000);
            this.subscribe();
        },

        destroy() {
            if (this._timer) clearInterval(this._timer);
        },

        subscribe() {
            if (!window.Echo) {
                console.warn('Echo not available');
                return;
            }
            const channel = window.Echo.private(`admin.${this.sellerId}.kds`);
            channel
                .subscribed(() => { this.connected = true; })
                .error(() => { this.connected = false; })
                .listen('.OrderPlaced', (e) => {
                    this.upsertTicket(e);
                    this.chime();
                    if (window.toast) window.toast.info(`New order: ${e.table_name || e.ticket_number}`);
                })
                .listen('.KitchenStatusUpdated', (e) => {
                    if (e.status === 'served' || e.status === 'cancelled') {
                        this.tickets = this.tickets.filter(t => t.ticket_id !== e.ticket_id);
                    } else {
                        this.upsertTicket(e);
                    }
                });
        },

        upsertTicket(payload) {
            const idx = this.tickets.findIndex(t => t.ticket_id === payload.ticket_id);
            const merged = { ...(idx >= 0 ? this.tickets[idx] : {}), ...payload };
            if (idx >= 0) {
                this.tickets.splice(idx, 1, merged);
            } else {
                this.tickets.push(merged);
            }
        },

        elapsedMinutes(ticket) {
            const start = new Date(ticket.fired_at || ticket.created_at).getTime();
            return Math.max(0, Math.floor((this.now - start) / 60000));
        },

        elapsedLabel(ticket) {
            const mins = this.elapsedMinutes(ticket);
            if (mins < 60) return mins + 'm';
            return Math.floor(mins / 60) + 'h ' + (mins % 60) + 'm';
        },

        urgencyBorder(ticket) {
            const m = this.elapsedMinutes(ticket);
            if (m >= 15) return 'border-red-500';
            if (m >= 5) return 'border-amber-400';
            return 'border-emerald-400';
        },

        urgencyHeader(ticket) {
            const m = this.elapsedMinutes(ticket);
            if (m >= 15) return 'bg-red-50 text-red-900';
            if (m >= 5) return 'bg-amber-50 text-amber-900';
            return 'bg-emerald-50 text-emerald-900';
        },

        chime() {
            if (!this.soundEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                gain.gain.value = 0.08;
                osc.start();
                setTimeout(() => { osc.stop(); ctx.close(); }, 180);
            } catch (e) {}
        },

        async setStatus(ticket, status) {
            this.busyId = ticket.ticket_id;
            try {
                const res = await window.axios.post(`/admin/kds/tickets/${ticket.ticket_id}/status`, { status });
                if (res.data?.ticket) {
                    if (status === 'served') {
                        this.tickets = this.tickets.filter(t => t.ticket_id !== ticket.ticket_id);
                    } else {
                        this.upsertTicket(res.data.ticket);
                    }
                } else {
                    ticket.status = status;
                }
            } catch (err) {
                const msg = err.response?.data?.message || 'Failed to update ticket';
                if (window.toast) window.toast.error(msg);
            } finally {
                this.busyId = null;
            }
        },
    };
}
</script>
@endpush


