@php
    use App\Enums\TableStatus;
    $tblStatus = $table->status instanceof TableStatus ? $table->status : TableStatus::tryFrom((string) $table->status);
    $tblStatusValue = $tblStatus?->value ?? (string) $table->status;
    $statusStyles = match ($tblStatus) {
        TableStatus::OCCUPIED => 'bg-red-50 text-red-700 border-red-200 hover:border-red-400 hover:bg-red-100/70',
        TableStatus::RESERVED
            => 'bg-amber-50 text-amber-800 border-amber-200 hover:border-amber-400 hover:bg-amber-100/70',
        default => 'bg-white text-slate-700 border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50',
    };
    $statusDot = match ($tblStatus) {
        TableStatus::OCCUPIED => 'bg-red-500',
        TableStatus::RESERVED => 'bg-amber-500',
        default => 'bg-emerald-500',
    };
    $statusIcon = match ($tblStatus) {
        TableStatus::OCCUPIED => 'ri-restaurant-fill',
        TableStatus::RESERVED => 'ri-time-fill',
        default => 'ri-sofa-line',
    };
@endphp

<div x-data="{ open: false }" class="contents">
    <button type="button"
        class="dining-table-card dining-table-chip inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-medium shadow-sm transition-all cursor-pointer {{ $statusStyles }}"
        data-table-id="{{ $table->id }}" data-status="{{ $tblStatusValue }}" @click="open = true">
        <span class="flex h-2 w-2 rounded-full {{ $statusDot }}"></span>
        <i class="{{ $statusIcon }} text-sm"></i>
        <span class="font-bold text-slate-900">{{ $table->name }}</span>
        <span
            class="dining-table-card-status text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.2 rounded bg-white/70 border border-slate-200/50">
            {{ $tblStatus?->label() ?? ucfirst($tblStatusValue) }}
        </span>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display:none">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden"
                @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center h-7 w-7 rounded-lg bg-orange-600 text-white text-xs"><i
                                class="ri-restaurant-line"></i></span>
                        Table: {{ $table->name }}
                    </h3>
                    <button type="button"
                        class="h-8 w-8 rounded-lg hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                        @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <form action="{{ route('admin.diningTables.update', $table->id) }}" method="post">
                    @csrf
                    <div class="p-5 space-y-4">
                        <div class="hidden">
                            <label class="text-xs font-semibold text-slate-700">Name</label>
                            <input name="name" type="text" class="form-control" value="{{ $table->name }}"
                                required>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-700 block mb-1.5">Change Table Status</label>
                            <select name="status"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 font-medium">
                                @foreach (\App\Models\DiningTable::statuses() as $status)
                                    <option value="{{ $status }}"
                                        {{ $tblStatusValue === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-3.5 border-t border-slate-100 bg-slate-50 rounded-b-3xl">
                        <button type="button"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-xl transition"
                            @click="open = false">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition shadow-sm flex items-center gap-1.5">
                            <i class="ri-check-line text-sm"></i> Save Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
