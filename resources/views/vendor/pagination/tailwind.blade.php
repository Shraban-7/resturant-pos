@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        {{-- Results info --}}
        <p class="text-sm text-slate-500">
            Showing
            @if ($paginator->firstItem())
                <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            of
            <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
            results
        </p>

        {{-- Buttons --}}
        <div class="inline-flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-300 cursor-not-allowed">
                    <i class="ri-arrow-left-s-line text-lg"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous" class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition">
                    <i class="ri-arrow-left-s-line text-lg"></i>
                </a>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center h-9 px-3 text-sm text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center h-9 min-w-[2.25rem] px-3 rounded-md bg-brand-600 text-white text-sm font-semibold shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="Go to page {{ $page }}" class="inline-flex items-center justify-center h-9 min-w-[2.25rem] px-3 rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 text-sm transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next" class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition">
                    <i class="ri-arrow-right-s-line text-lg"></i>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-slate-200 bg-white text-slate-300 cursor-not-allowed">
                    <i class="ri-arrow-right-s-line text-lg"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
