<x-admin-layout :title="$message->subject">
    <div class="card p-5 max-w-3xl space-y-4">
        <div class="flex flex-wrap justify-between gap-2 text-body-md">
            <div>
                <p class="font-semibold">{{ $message->name }}</p>
                <p class="text-muted">{{ $message->email }}{{ $message->phone ? ' · '.$message->phone : '' }}</p>
            </div>
            <p class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <p class="text-body-lg whitespace-pre-line border-t border-line-soft pt-4">{{ $message->message }}</p>
        <div class="flex flex-wrap gap-2 border-t border-line-soft pt-4">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}" class="btn-primary"><span class="material-symbols-outlined text-[18px]">reply</span> Responder por correo</a>
            <a href="{{ route('admin.messages.index') }}" class="btn-secondary">Volver</a>
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" class="ml-auto" onsubmit="return confirm('¿Eliminar este mensaje?')">
                @csrf @method('DELETE')
                <button class="btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</x-admin-layout>
