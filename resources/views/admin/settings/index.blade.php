@extends('layouts.admin')
@section('title', 'Settings')
@section('page_title', 'Settings')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Settings</span>
@endsection

@section('content')

@php
    $tabs = [
        'business' => ['ri-store-2-line', 'Business', 'Logo and contact details'],
        'profile'  => ['ri-user-3-line', 'Profile', 'Your account and password'],
        'pos'      => ['ri-calculator-line', 'POS', 'VAT and default discount'],
        'receipt'  => ['ri-bill-line', 'Receipt', 'Printed ticket header and footer'],
    ];
@endphp

<div class="page-header">
    <div>
        <p class="page-subtitle">Restaurant identity, cashier rules, and what prints on the guest ticket.</p>
    </div>
</div>

<div x-data="{
        tab: '{{ $activeTab ?? 'business' }}',
        header: {{ \Illuminate\Support\Js::from(old('receipt_header', $business->receipt_header ?? '')) }},
        footer: {{ \Illuminate\Support\Js::from(old('receipt_footer', $business->receipt_footer ?? '')) }},
     }" x-cloak class="grid grid-cols-1 lg:grid-cols-[240px_minmax(0,1fr)] gap-6 items-start">
    <nav class="card p-2 lg:sticky lg:top-20" role="tablist" aria-label="Settings sections">
        <div class="flex lg:flex-col gap-1 overflow-x-auto">
            @foreach ($tabs as $key => [$icon, $label, $hint])
                <button type="button" role="tab"
                        :aria-selected="tab === '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'bg-brand-50 text-brand-700 ring-1 ring-brand-100 shadow-xs'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                        class="flex items-center gap-3 w-full text-left rounded-xl px-3 py-2.5 transition-all duration-200 shrink-0"
                        @click="tab = '{{ $key }}'; history.replaceState(null, '', '{{ route('admin.settings.index') }}?tab={{ $key }}')">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white border border-slate-200 text-base shrink-0 transition-all duration-200"
                          :class="tab === '{{ $key }}' ? 'border-brand-200 text-brand-600 shadow-xs' : ''">
                        <i class="{{ $icon }}"></i>
                    </span>
                    <span class="min-w-0 hidden sm:block">
                        <span class="block text-sm font-semibold leading-tight">{{ $label }}</span>
                        <span class="block text-[11px] text-slate-500 truncate mt-0.5">{{ $hint }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </nav>

    <div class="min-w-0">
        {{-- ==================== BUSINESS SETTINGS ==================== --}}
        <div x-show="tab === 'business'"
             x-transition:enter="transition-all ease-out duration-250 motion-reduce:transition-none"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.business') }}">
                @csrf
                <input type="hidden" name="tab" value="business">

                <div class="card mb-4 overflow-hidden">
                    <div class="card-header">
                        <div>
                            <h6 class="card-title">Business logo</h6>
                            <p class="card-subtitle">Square PNG or JPEG, 500×500 or 1000×1000.</p>
                        </div>
                    </div>
                    <div class="card-body flex flex-col sm:flex-row items-center sm:items-end gap-4">
                        <div class="h-28 w-28 rounded-2xl border border-dashed border-slate-300 bg-slate-50 bg-center bg-cover bg-no-repeat shrink-0"
                             id="img-preview"
                             style="background-image: url('{{ isset($business->image) ? storage_url($business->image) : '' }}');">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="form-label">Upload image</label>
                            <input class="form-control" name="image" type="file" accept="image/*"
                                   @change="const r=new FileReader(); r.onload=e=>{ document.getElementById('img-preview').style.backgroundImage='url('+e.target.result+')'; }; r.readAsDataURL($event.target.files[0]);">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h6 class="card-title">Business information</h6>
                            <p class="card-subtitle">General contact and tax identification details.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Business name</label>
                                <input type="text" name="name" class="form-control" required autocomplete="off" value="{{ old('name', $business->name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">VAT number</label>
                                <input type="text" name="vat_number" class="form-control" autocomplete="off" value="{{ old('vat_number', $business->vat_number ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required autocomplete="off" value="{{ old('email', $business->email ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" required autocomplete="off" value="{{ old('phone', $business->phone ?? '') }}">
                            </div>
                            <div class="form-group md:col-span-2 mb-0">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" required autocomplete="off" value="{{ old('address', $business->address ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Save business
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== PROFILE SETTINGS ==================== --}}
        <div x-show="tab === 'profile'"
             x-transition:enter="transition-all ease-out duration-250 motion-reduce:transition-none"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <form method="POST" action="{{ route('admin.settings.profile') }}">
                @csrf
                <input type="hidden" name="tab" value="profile">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">Account</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required autocomplete="off" value="{{ old('name', $user->name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required autocomplete="off" value="{{ old('email', $user->email ?? '') }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" autocomplete="off" value="{{ old('phone', $user->phone ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">Password</h6>
                        </div>
                        <div class="card-body">
                            <p class="form-hint mb-3">Leave blank to keep your current password.</p>
                            <div class="form-group">
                                <label class="form-label">Current password</label>
                                <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                            </div>
                            <div class="form-group">
                                <label class="form-label">New password</label>
                                <input type="password" name="password" class="form-control" autocomplete="new-password">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">Confirm new password</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Save profile
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== POS SETTINGS ==================== --}}
        <div x-show="tab === 'pos'"
             x-transition:enter="transition-all ease-out duration-250 motion-reduce:transition-none"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <form method="POST" action="{{ route('admin.settings.pos') }}">
                @csrf
                <input type="hidden" name="tab" value="pos">

                <div class="card mb-4">
                    <div class="card-header">
                        <div>
                            <h6 class="card-title">VAT / tax</h6>
                            <p class="card-subtitle">Applied on POS checkout totals.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="vat_enabled" value="1" class="mt-0.5 rounded border-slate-300"
                                       @checked((bool) old('vat_enabled', $business->vat_enabled ?? false))>
                                <span>
                                    <span class="block text-sm font-medium text-slate-800">Enable VAT</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Show and charge VAT on orders</span>
                                </span>
                            </label>
                            <div>
                                <label class="form-label">VAT rate (%)</label>
                                <input type="number" name="vat_rate" class="form-control" min="0" max="100" step="0.01"
                                       value="{{ old('vat_rate', $business->vat_rate ?? 0) }}">
                            </div>
                            <div>
                                <label class="form-label">VAT mode</label>
                                <select name="vat_mode" class="form-select">
                                    <option value="exclusive" @selected(old('vat_mode', $business->vat_mode ?? 'exclusive') === 'exclusive')>Exclusive — added on top</option>
                                    <option value="inclusive" @selected(old('vat_mode', $business->vat_mode ?? '') === 'inclusive')>Inclusive — already in price</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h6 class="card-title">Default discount</h6>
                            <p class="card-subtitle">Applies to all POS products. A cashier-entered discount overrides this.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="global_discount_enabled" value="1" class="mt-0.5 rounded border-slate-300"
                                       @checked((bool) old('global_discount_enabled', $business->global_discount_enabled ?? false))>
                                <span>
                                    <span class="block text-sm font-medium text-slate-800">Enable discount</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Auto-apply on new orders</span>
                                </span>
                            </label>
                            <div>
                                <label class="form-label">Type</label>
                                <select name="global_discount_type" class="form-select">
                                    <option value="percentage" @selected(old('global_discount_type', $business->global_discount_type ?? 'percentage') === 'percentage')>Percentage (%)</option>
                                    <option value="flat" @selected(old('global_discount_type', $business->global_discount_type ?? '') === 'flat')>Flat (৳)</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Value</label>
                                <input type="number" name="global_discount_rate" class="form-control" min="0" step="0.01"
                                       value="{{ old('global_discount_rate', $business->global_discount_rate ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Save POS
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== RECEIPT SETTINGS ==================== --}}
        <div x-show="tab === 'receipt'"
             x-transition:enter="transition-all ease-out duration-250 motion-reduce:transition-none"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <form method="POST" action="{{ route('admin.settings.receipt') }}">
                @csrf
                <input type="hidden" name="tab" value="receipt">

                <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_280px] gap-4">
                    <div class="space-y-4">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h6 class="card-title">Receipt header</h6>
                                    <p class="card-subtitle">Prints under the restaurant name, address, and phone.</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <textarea name="receipt_header" rows="4" maxlength="500" class="form-control font-mono text-sm"
                                          placeholder="VAT ID: 123456789&#10;www.yourrestaurant.com"
                                          x-model="header">{{ old('receipt_header', $business->receipt_header ?? '') }}</textarea>
                                <p class="form-hint">Optional extra lines. One line per row. Max 500 characters.</p>
                                @error('receipt_header')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h6 class="card-title">Receipt footer</h6>
                                    <p class="card-subtitle">Prints at the bottom of the ticket. No signature is printed.</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <textarea name="receipt_footer" rows="4" maxlength="255" class="form-control font-mono text-sm"
                                          placeholder="THANK YOU!&#10;Please come again"
                                          x-model="footer">{{ old('receipt_footer', $business->receipt_footer ?? '') }}</textarea>
                                <p class="form-hint">Thank-you line, Wi-Fi password, return policy — whatever guests should see last. Max 255 characters.</p>
                                @error('receipt_footer')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <aside class="card overflow-hidden xl:sticky xl:top-20">
                        <div class="card-header">
                            <h6 class="card-title">Ticket preview</h6>
                        </div>
                        <div class="card-body bg-slate-100/80">
                            <div class="mx-auto bg-white text-slate-900 shadow-sm border border-slate-200 px-3 py-4 text-center"
                                 style="width: 220px; font-family: 'Courier New', ui-monospace, monospace; font-size: 11px; line-height: 1.35;">
                                <div class="font-bold text-sm uppercase tracking-wide mb-1">{{ $business->name ?? 'Restaurant Name' }}</div>
                                <div class="text-[10px] text-slate-600">{{ $business->address ?? 'Address' }}</div>
                                <div class="text-[10px] text-slate-600 mb-1">Tel: {{ $business->phone ?? '—' }}</div>
                                <div class="whitespace-pre-line text-[10px] text-slate-700 mb-2" x-text="header" x-show="header.trim().length"></div>
                                <div class="border-t border-dashed border-slate-400 my-2"></div>
                                <div class="font-bold underline mb-1">SALES INVOICE</div>
                                <div class="flex justify-between text-[10px] mb-2">
                                    <span>#INV-0001</span>
                                    <span>Preview</span>
                                </div>
                                <div class="border-t border-dashed border-slate-400 my-2"></div>
                                <div class="flex justify-between text-[10px]"><span>Sample item</span><span>1 × ৳120</span></div>
                                <div class="border-t border-slate-800 mt-2 pt-1 flex justify-between font-bold text-[11px]"><span>TOTAL</span><span>৳120</span></div>
                                <div class="border-t border-dashed border-slate-400 my-2"></div>
                                <div class="whitespace-pre-line font-bold text-[11px]" x-text="footer.trim() ? footer : 'THANK YOU!'"></div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Save receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
