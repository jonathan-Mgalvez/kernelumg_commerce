<x-admin-layout title="Mensajes de contacto">
    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[640px]">
            <thead><tr><th></th><th>Remitente</th><th>Asunto</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
                @forelse ($messages as $m)
                    <tr class="{{ $m->read ? '' : 'font-semibold' }}">
                        <td class="w-8">@unless($m->read)<span class="block w-2 h-2 bg-warn" title="Sin leer"></span>@endunless</td>
                        <td>{{ $m->name }}<p class="text-body-sm text-muted font-normal">{{ $m->email }}</p></td>
                        <td>{{ $m->subject }}</td>
                        <td class="whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right"><a href="{{ route('admin.messages.show', $m) }}" class="btn-secondary btn-sm">Abrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-8">No hay mensajes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
</x-admin-layout>
