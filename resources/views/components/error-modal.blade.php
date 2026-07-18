<div x-data="{ open: false, message: '' }"
     @open-error.window="open = true; message = $event.detail"
     @keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>
    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-red-200" @click.stop>
        <div class="flex items-start gap-3 p-5">
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 text-red-600 shrink-0">
                <i class="ri-error-warning-line text-2xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-base text-slate-900">Error</h3>
                <p class="text-sm text-slate-600 mt-1 errorMsg" x-text="message"></p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" @click="open = false" aria-label="Close">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <div class="flex justify-end px-5 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
            <button type="button" class="btn btn-secondary btn-sm" @click="open = false">OK</button>
        </div>
    </div>
</div>
