@extends('layouts.admin')
@section('title', 'Settings')
@section('page_title', 'Settings')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Settings</span>
@endsection

@push('header')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature-pad.css">
@endpush

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
@endpush

@section('content')

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.index') }}" id="form"
      x-data="{ preview: '{{ isset($business->image) ? storage_url($business->image) : '' }}' }">
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
                       @change="const r=new FileReader(); r.onload=e=>{ document.getElementById('img-preview').style.backgroundImage='url('+e.target.result+')'; }; r.readAsDataURL($event.target.files[0]);">
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4"
         x-data="{
            canvas: null,
            pad: null,
            init() {
                this.canvas = document.getElementById('signature-pad');
                window.addEventListener('resize', () => this.resize());
                requestAnimationFrame(() => {
                    if (!this.canvas) return;
                    this.resize();
                    this.pad = new SignaturePad(this.canvas, { onEnd: () => this.setSig() });
                });
            },
            resize() {
                if (!this.canvas || this.canvas.offsetWidth === 0) return;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                this.canvas.width = this.canvas.offsetWidth * ratio;
                this.canvas.height = this.canvas.offsetHeight * ratio;
                this.canvas.getContext('2d').scale(ratio, ratio);
            },
            setSig() {
                if (!this.pad) return;
                document.getElementById('signature').value = this.pad.toDataURL('image/png');
            },
            clear() {
                if (!this.pad) return;
                this.pad.clear();
                document.getElementById('signature').value = '';
            }
         }">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Draw Signature</h6>
            </div>
            <div class="card-body">
                <div class="border border-slate-200 bg-white rounded-lg overflow-hidden" style="max-width: 400px;">
                    <canvas id="signature-pad" class="signature-pad block w-full" style="height: 200px; touch-action: none;"></canvas>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <button type="button" class="btn btn-dark btn-sm" @click="clear()">
                        <i class="ri-eraser-line"></i> Clear
                    </button>
                </div>
                <input type="hidden" id="signature" name="signature">
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Saved Signature</h6>
            </div>
            <div class="card-body">
                @isset($business->signature)
                    <img src="{{ storage_url($business->signature) }}" alt="signature" class="rounded border border-slate-200 max-w-full" style="max-width: 400px;">
                @else
                    <p class="text-sm text-slate-500">No signature saved yet.</p>
                @endisset
            </div>
        </div>
    </div>

    <div class="mt-4 flex justify-end gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="ri-save-line"></i> {{ is_null($business) ? 'Save Settings' : 'Update Settings' }}
        </button>
    </div>
</form>

@endsection

