@if ($message = Session::get('success'))
    <x-alert :type="'success'">{{ $message }}</x-alert>
@endif

@if ($message = Session::get('error'))
    <x-alert :type="'danger'">{{ $message }}</x-alert>
@endif

@if ($message = Session::get('warning'))
    <x-alert :type="'warning'">{{ $message }}</x-alert>
@endif

@if ($message = Session::get('info'))
    <x-alert :type="'info'">{{ $message }}</x-alert>
@endif

@if ($errors->any())
    <div class="alert alert-danger my-3" role="alert" x-data="{ open: true }" x-show="open">
        <ul class="flex-1 list-disc pl-4">
            @foreach($errors->getMessages() as $key => $value)
                <li>{{ $value[0] }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
    </div>
@endif
