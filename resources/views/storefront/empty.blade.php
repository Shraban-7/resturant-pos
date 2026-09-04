<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Opening soon</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <div class="text-center max-w-sm">
        <p class="text-5xl">🍽️</p>
        <h1 class="text-xl font-bold text-slate-900 mt-4">Storefront opening soon</h1>
        <p class="text-sm text-slate-500 mt-1">Create an admin account and add products to open the public store.</p>
        <a href="{{ route('login') }}" class="inline-block mt-5 bg-slate-900 text-white text-sm font-semibold px-5 py-2.5 rounded-full">Staff Login</a>
    </div>
</body>
</html>
