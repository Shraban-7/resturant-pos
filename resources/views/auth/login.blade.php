@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')

<div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8">
    <div class="text-center mb-6">
        @if (store_logo_url())
            <img src="{{ store_logo_url() }}" alt="{{ store_name() }}" class="h-14 w-14 rounded-2xl object-cover mx-auto mb-3 shadow-md">
        @else
            <span class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-brand-600 text-white mb-3">
                <i class="ri-restaurant-2-line text-2xl"></i>
            </span>
        @endif
        <h1 class="text-2xl font-semibold text-slate-900">{{ store_name() }}</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to your account to continue</p>
    </div>

    <form method="POST" action="">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" class="form-control" name="password" placeholder="Your password" required>
        </div>

        <div class="form-group flex items-center justify-between">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" id="remembercheck" name="remember" class="form-check-input">
                <span class="form-check-label">Remember me</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-full mt-2">
            <i class="ri-login-box-line"></i> Sign in
        </button>

        {{-- <p class="mt-6 text-sm text-center text-slate-500">
            New here?
            <a class="text-brand-600 hover:underline font-medium" href="{{ route('register') }}">Create an account</a>
        </p> --}}
    </form>
</div>

@endsection
