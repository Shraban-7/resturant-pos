@php
    $type = $type ?? 'info';
@endphp
<div class="alert alert-{{ $type }} my-2" role="alert" x-data="{ open: true }" x-show="open">
    <strong class="flex-1">{{ $slot }}</strong>
    <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
</div>
