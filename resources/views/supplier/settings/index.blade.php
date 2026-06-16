@extends('layouts.admin')
@section('title', 'Settings')
@section('page_title', 'Settings')
@section('breadcrumb')
<a href="{{ route('supplier.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Settings</span>
@endsection

@section('content')

<form method="POST" enctype="multipart/form-data" action="{{ route('supplier.settings.index') }}" id="form">
    @csrf

    <div class="card mb-4">
        <div class="card-body flex flex-col sm:flex-row items-end gap-4">
            <div>
                <div class="border bg-slate-50 bg-no-repeat bg-center bg-cover rounded-lg"
                     id="img-preview"
                     style="background-image: url('{{ isset($business->image) ? storage_url($business->image) : '' }}'); height: 120px; width: 120px;">
                </div>
            </div>
            <div class="flex-1">
                <label class="form-label">Business Logo</label>
                <p class="form-hint">Size: 500x500px or 1000x1000px (square), PNG/JPEG only.</p>
                <input class="form-control" name="image" type="file" id="img-input" accept="image/*"
                       onchange="const r=new FileReader(); r.onload=e=>{ document.getElementById('img-preview').style.backgroundImage='url('+e.target.result+')'; }; r.readAsDataURL(this.files[0]);">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Business Information</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Business Name</label>
                    <input type="text" name="name" class="form-control" required autocomplete="off" value="{{ $business->name ?? '' }}">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Business Email</label>
                        <input type="email" name="email" class="form-control" required autocomplete="off" value="{{ $business->email ?? '' }}">
                    </div>
                    <div>
                        <label class="form-label">Business Phone</label>
                        <input type="text" name="phone" class="form-control" required autocomplete="off" value="{{ $business->phone ?? '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" required autocomplete="off" value="{{ $business->address ?? '' }}">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">VAT Number</label>
                    <input type="text" name="vat_number" class="form-control" autocomplete="off" value="{{ $business->vat_number ?? '' }}">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Bank Information</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" autocomplete="off" value="{{ $business->bank_name ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Holder Name</label>
                    <input type="text" name="account_holder" class="form-control" autocomplete="off" value="{{ $business->account_holder ?? '' }}">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Account No.</label>
                    <input type="text" name="account_number" class="form-control" autocomplete="off" value="{{ $business->account_number ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex justify-end gap-2">
        <button type="submit" class="btn btn-grad">
            <i class="ri-save-line"></i> {{ is_null($business) ? 'Save Settings' : 'Update Settings' }}
        </button>
    </div>
</form>

@endsection
