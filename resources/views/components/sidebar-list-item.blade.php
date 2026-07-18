@php
    $isActive = request()->routeIs($route);
@endphp
<li>
    <a href="{{ route($route) }}"
       class="sidebar-link {{ $isActive ? 'active' : '' }}">
        @isset($icon)<i class="{{ $icon }}"></i>@endisset
        <span class="menu-title">{{ $title }}</span>
    </a>
</li>
