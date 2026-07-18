<div x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" class="btn btn-secondary" @click="open = true">
        <i class="ri-filter-3-line"></i> Filter
    </button>
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50" style="display:none">
            <div class="offcanvas-backdrop" @click="open = false"></div>
            <aside class="offcanvas" @click.stop>
                <div class="offcanvas-header">
                    <h5 class="text-base font-semibold">Filter</h5>
                    <button type="button" class="text-gray-500 hover:text-gray-800" @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                <div class="offcanvas-body">
                    <form action="">
                        {{ $slot }}
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button type="submit" class="btn btn-primary w-full">Filter</button>
                            <a href="{{ url(request()->path()) }}" class="btn btn-secondary w-full">Clear</a>
                        </div>
                    </form>
                </div>
            </aside>
        </div>
    </template>
</div>
