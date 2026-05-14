<nav class="bg-white rounded-md shadow-sm mb-2 px-3 py-2 flex items-center justify-between gap-2">
    <div class="flex items-center gap-2">
        @if(auth()->user()->role == 'seller')
            <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('assets/images/icons/cart.png') }}" class="h-8" alt="logo" />
            </a>
            <a class="nav-link" href="{{ route('seller.dashboard') }}">Dashboard</a>
        @else
            <a href="{{ route('supplier.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('assets/images/icons/cart.png') }}" class="h-8" alt="logo" />
            </a>
            <a class="nav-link" href="{{ route('supplier.dashboard') }}">Dashboard</a>
        @endif
    </div>

    <div class="hidden md:flex items-center gap-2">
        <button id="fullscreen-btn" class="btn btn-primary btn-sm" title="Fullscreen">
            <i class="ri-fullscreen-fill"></i>
        </button>
        <button id="refresh-btn" class="btn btn-secondary btn-sm" title="Refresh">
            <i class="ri-loop-right-fill"></i>
        </button>

        <div class="input-group">
            <span class="input-group-text"><i class="ri-search-line"></i></span>
            <input type="text" class="form-control" placeholder="Product Name" id="productNameSearch">
        </div>
    </div>
</nav>
