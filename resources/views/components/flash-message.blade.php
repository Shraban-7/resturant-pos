{{-- Server flash messages rendered as toasts (window.toast from app.js). --}}
@php
    $toasts = [];
    foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type) {
        if ($message = Session::get($key)) {
            $toasts[] = ['type' => $type, 'message' => $message];
        }
    }
    if ($errors->any()) {
        foreach ($errors->all() as $message) {
            $toasts[] = ['type' => 'error', 'message' => $message];
        }
    }
@endphp

@if (! empty($toasts))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach ($toasts as $toast)
                window.toast?.{{ $toast['type'] }}(@js($toast['message']));
            @endforeach
        });
    </script>
@endif
