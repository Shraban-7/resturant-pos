@extends('layouts.admin')
@section('title', 'Storefront')
@section('page_title', 'Storefront Settings')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Storefront</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Everything guests see on your public store page. Empty fields fall back to defaults.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('storefront.index') }}" target="_blank" class="btn btn-secondary"><i class="ri-external-link-line"></i> View Store</a>
    </div>
</div>

<form action="{{ route('admin.storefront-settings.update') }}" method="post">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Announcement Bar</h6>
                    <p class="card-subtitle">A strip above the store, e.g. festival offers</p>
                </div>
                <label class="flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" name="announcement_enabled" value="1" @checked(($settings['announcement_enabled'] ?? '0') === '1') class="rounded">
                    Show
                </label>
            </div>
            <div class="card-body">
                <label class="form-label">Announcement text</label>
                <input type="text" name="announcement_text" class="form-control" value="{{ $settings['announcement_text'] ?? '' }}" placeholder="Eid special: 10% off on dinner buffet">
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Hero Section</h6>
                    <p class="card-subtitle">Blank title uses your business name</p>
                </div>
            </div>
            <div class="card-body space-y-3">
                <div>
                    <label class="form-label">Title</label>
                    <input type="text" name="hero_title" class="form-control" value="{{ $settings['hero_title'] ?? '' }}" placeholder="{{ config('app.name') }}">
                </div>
                <div>
                    <label class="form-label">Subtitle</label>
                    <textarea name="hero_subtitle" rows="2" class="form-control" placeholder="Leave blank for the default tagline">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="form-label">Opening hours line</label>
                    <input type="text" name="opening_hours" class="form-control" value="{{ $settings['opening_hours'] ?? '' }}">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Sections</h6>
                    <p class="card-subtitle">Show or hide store blocks</p>
                </div>
            </div>
            <div class="card-body space-y-2">
                @foreach (['show_popular' => 'Popular dishes strip', 'show_branches' => 'Find-us branches', 'show_reservation' => 'Table reservation form'] as $key => $label)
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="{{ $key }}" value="1" @checked(($settings[$key] ?? '1') === '1') class="rounded">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Footer</h6>
                    <p class="card-subtitle">Small note under the store</p>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label">Footer note</label>
                <input type="text" name="footer_note" class="form-control" value="{{ $settings['footer_note'] ?? '' }}" placeholder="Home delivery inside Dhaka">
            </div>
        </div>
    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Storefront</button>
    </div>
</form>

@endsection
