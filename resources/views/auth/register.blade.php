@extends('layouts.auth')
@section('title', 'Create account')
@section('content')

<div class="bg-white rounded-2xl shadow-xl border border-slate-200/60 p-8">
    <div class="text-center mb-6">
        <span class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-brand-600 text-white mb-3">
            <i class="ri-restaurant-2-line text-2xl"></i>
        </span>
        <h1 class="text-2xl font-semibold text-slate-900">Create your account</h1>
        <p class="mt-1 text-sm text-slate-500">Get started in just a few seconds</p>
    </div>

    <form method="POST" action="">
        @csrf
        <div class="form-group">
            <label class="form-label">Full name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="John Doe" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Create a password" required>
        </div>

        <div class="form-group">
            <label class="form-label">Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn btn-primary w-full mt-2">
            <i class="ri-user-add-line"></i> Create account
        </button>

        <p class="mt-6 text-sm text-center text-slate-500">
            Already have an account?
            <a class="text-brand-600 hover:underline font-medium" href="{{ route('login') }}">Sign in</a>
        </p>
    </form>
</div>

@endsection
