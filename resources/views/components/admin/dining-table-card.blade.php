@php
    use \App\Enums\TableStatus;
    $statusStyles = match ($table->status) {
        TableStatus::OCCUPIED => 'bg-red-50 text-red-700 border-red-200 hover:border-red-300',
        TableStatus::RESERVED => 'bg-amber-50 text-amber-800 border-amber-200 hover:border-amber-300',
        default              => 'bg-white text-slate-700 border-slate-200 hover:border-slate-300',
    };
    $statusIcon = match ($table->status) {
        TableStatus::OCCUPIED => 'ri-sofa-fill',
        TableStatus::RESERVED => 'ri-time-line',
        default               => 'ri-sofa-line',
    };
@endphp

<div x-data="{ open: false }" class="contents">
    <button type="button"
            class="dining-table-card dining-table-chip {{ $statusStyles }}"
            data-table-id="{{ $table->id }}"
            data-status="{{ $table->status->value }}"
            @click="open = true">
        <i class="{{ $statusIcon }} text-base"></i>
        <span class="font-semibold">{{ $table->name }}</span>
        <span class="dining-table-card-status">{{ $table->status->label() }}</span>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200" @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <i class="ri-restaurant-line text-brand-600"></i>
                        Update {{ $table->name }}
                    </h3>
                    <button type="button" class="btn btn-ghost btn-icon" @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                <form action="{{ route('admin.diningTables.update', $table->id) }}" method="post">
                    @csrf
                    <div class="px-5 py-4 space-y-3">
                        <div class="hidden">
                            <label class="form-label">Name</label>
                            <input name="name" type="text" class="form-control" value="{{ $table->name }}" required>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach (\App\Models\DiningTable::statuses() as $status)
                                    <option value="{{ $status }}" {{ $table->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                        <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>



