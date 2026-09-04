<div class="flex items-center gap-3 min-w-0">
    <button type="button" class="lg:hidden btn btn-ghost btn-icon -ml-2" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
        <i class="ri-menu-line text-xl"></i>
    </button>

    <div class="min-w-0">
        <h1 class="text-base sm:text-lg font-semibold text-slate-900 truncate">@yield('page_title', 'Dashboard')</h1>
        <div class="text-xs text-slate-500 truncate">
            @hasSection('breadcrumb')
                @yield('breadcrumb')
            @else
                @yield('title')
            @endif
        </div>
    </div>
</div>

<div class="flex items-center gap-2">
    @if (auth()->check() && admin_branches()->isNotEmpty())
        <form action="{{ route('admin.branches.switch') }}" method="post" class="hidden sm:block">
            @csrf
            <select name="branch_id" class="form-control form-control-sm min-w-[10rem]" onchange="this.form.submit()">
                <option value="" @selected(is_all_branches_mode())>All branches</option>
                @foreach (admin_branches() as $branch)
                    <option value="{{ $branch->id }}" @selected((int) active_branch_id() === (int) $branch->id && ! is_all_branches_mode())>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif

    @php
        $navNotifications = \App\Models\StaffNotification::forOwner()->latest('id')->take(8)->get();
        $navUnread = \App\Models\StaffNotification::forOwner()->unread()->count();
    @endphp
    <div class="hidden sm:flex items-center rounded-full border border-slate-200 overflow-hidden text-[11px] font-bold" title="Language">
        <a href="{{ route('lang.switch', 'en') }}" class="px-2 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">EN</a>
        <a href="{{ route('lang.switch', 'bn') }}" class="px-2 py-1.5 {{ app()->getLocale() === 'bn' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">বাং</a>
    </div>

    <div class="relative" x-data="notifBell()" @keydown.escape.window="open = false" @notif-live.window="await refresh(false)">
        <button type="button" class="btn btn-ghost btn-icon" aria-label="Notifications" @click="toggle()">
            <i class="ri-notification-3-line text-lg"></i>
            <span x-show="unread > 0" x-text="unread > 9 ? '9+' : unread" x-cloak
                  class="absolute -top-0.5 -right-0.5 min-h-[1.1rem] min-w-[1.1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"></span>
        </button>
        <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu !p-0 w-[22rem] max-w-[90vw]" style="display:none">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <p class="font-semibold text-sm text-slate-900">Notifications</p>
                <button type="button" @click="readAll()" class="text-xs font-semibold text-brand-600 hover:underline">Mark all read</button>
            </div>
            <div class="max-h-[22rem] overflow-y-auto" id="notif-list">
                <template x-for="n in items" :key="n.id">
                    <div class="flex gap-3 px-4 py-3 border-b border-slate-50 hover:bg-slate-50" :class="n.read ? '' : 'bg-orange-50/50'">
                        <span class="shrink-0 flex items-center justify-center h-9 w-9 rounded-full" :class="n.color"><i :class="n.icon" class="text-lg"></i></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate" x-text="n.title"></p>
                            <p class="text-xs text-slate-500 line-clamp-2" x-text="n.body"></p>
                            <p class="text-[11px] text-slate-400 mt-0.5" x-text="n.time"></p>
                        </div>
                    </div>
                </template>
                <div x-show="!items.length" class="px-4 py-8 text-center text-slate-500">
                    <i class="ri-notification-off-line text-3xl"></i>
                    <p class="text-sm font-medium mt-1">No notifications yet</p>
                </div>
            </div>
            <a href="{{ route('admin.notifications.index') }}" class="block text-center text-xs font-semibold text-slate-600 hover:text-brand-600 py-2.5 border-t border-slate-100">View all</a>
        </div>
    </div>

    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-slate-100 transition" @click="open = !open" :aria-expanded="open">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-semibold">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </span>
            <span class="hidden md:flex flex-col items-start leading-tight">
                <span class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'User' }}</span>
                <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ is_employee() ? 'Employee' : 'Admin' }}</span>
            </span>
            <i class="ri-arrow-down-s-line text-slate-400"></i>
        </button>
        <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu" style="display:none">
            <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                <i class="ri-settings-3-line me-2"></i> Settings
            </a>
            <div class="border-t border-slate-100 my-1"></div>
            <a href="{{ route('logout') }}" class="dropdown-item text-red-600">
                <i class="ri-logout-box-r-line me-2"></i> Logout
            </a>
        </div>
    </div>
</div>


