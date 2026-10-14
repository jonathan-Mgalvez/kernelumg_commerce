@if (session('status') || $errors->any())
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pt-4 space-y-2">
        @if (session('status'))
            <div class="flex items-start gap-2 border border-ok/30 bg-ok/10 text-ok px-4 py-3 rounded text-body-md" role="status">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="flex items-start gap-2 border border-danger/30 bg-danger/10 text-danger px-4 py-3 rounded text-body-md" role="alert">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <ul class="list-none space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
