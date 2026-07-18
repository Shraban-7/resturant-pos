@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" x-data="{ open: true }" x-show="open">
        <strong class="flex-1">{{ $message }}</strong>
        <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
    </div>
@endif

@if ($message = Session::get('error'))
    <div class="alert alert-danger alert-dismissible fade show" x-data="{ open: true }" x-show="open">
        <strong class="flex-1">{{ $message }}</strong>
        <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
    </div>
@endif

@if ($message = Session::get('warning'))
    <div class="alert alert-warning alert-dismissible fade show" x-data="{ open: true }" x-show="open">
        <strong class="flex-1">{{ $message }}</strong>
        <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
    </div>
@endif

@if ($message = Session::get('info'))
    <div class="alert alert-info alert-dismissible fade show" x-data="{ open: true }" x-show="open">
        <strong class="flex-1">{{ $message }}</strong>
        <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" x-data="{ open: true }" x-show="open">
        <ul class="flex-1 list-disc pl-4">
            @foreach($errors->getMessages() as $key => $value)
                <li>{{ $value[0] }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" @click="open = false" aria-label="Close">×</button>
    </div>
@endif
