<x-admin-layout title="Auditoría">
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="Usuario o correo..." class="input w-56">
        <select name="entidad" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todas las entidades</option>
            @foreach (\App\Models\Audit::ENTIDADES as $clase => $nombre)
                <option value="{{ $clase }}" @selected(request('entidad') === $clase)>{{ $nombre }}</option>
            @endforeach
        </select>
        <select name="accion" class="input w-auto pr-8" onchange="this.form.submit()">
            <option value="">Todas las acciones</option>
            @foreach (['creado', 'actualizado', 'eliminado'] as $a)
                <option value="{{ $a }}" @selected(request('accion') === $a)>{{ ucfirst($a) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[860px]">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Registro</th><th>Cambios</th><th>IP</th></tr></thead>
            <tbody>
                @forelse ($audits as $a)
                    <tr class="align-top">
                        <td class="whitespace-nowrap text-body-sm">{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $a->user->name ?? 'Sistema' }}<p class="text-body-sm text-muted">{{ $a->user?->role_label }}</p></td>
                        <td><span class="{{ $a->action_chip }}">{{ $a->action }}</span></td>
                        <td class="whitespace-nowrap">{{ $a->entidad }} #{{ $a->auditable_id }}</td>
                        <td class="text-body-sm max-w-md">
                            <div x-data="{ abierto: false }">
                                @foreach (array_slice($a->cambios, 0, 3, true) as $campo => [$antes, $despues])
                                    <p class="truncate"><span class="text-muted">{{ $campo }}:</span>
                                        @if ($a->action === 'actualizado')<span class="line-through text-danger/80">{{ Str::limit(json_encode($antes, JSON_UNESCAPED_UNICODE), 40) }}</span> →@endif
                                        <span class="{{ $a->action === 'eliminado' ? 'text-danger' : 'text-ok' }}">{{ Str::limit(json_encode($a->action === 'eliminado' ? $antes : $despues, JSON_UNESCAPED_UNICODE), 60) }}</span>
                                    </p>
                                @endforeach
                                @if (count($a->cambios) > 3)
                                    <div x-show="abierto" x-cloak>
                                        @foreach (array_slice($a->cambios, 3, null, true) as $campo => [$antes, $despues])
                                            <p class="truncate"><span class="text-muted">{{ $campo }}:</span> {{ Str::limit(json_encode($a->action === 'eliminado' ? $antes : $despues, JSON_UNESCAPED_UNICODE), 60) }}</p>
                                        @endforeach
                                    </div>
                                    <button type="button" class="text-label-sm uppercase text-primary-container hover:underline" @click="abierto = !abierto"
                                            x-text="abierto ? 'Ver menos' : '+{{ count($a->cambios) - 3 }} campos'"></button>
                                @endif
                            </div>
                        </td>
                        <td class="text-body-sm text-muted">{{ $a->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-8">Sin registros de auditoría.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $audits->links() }}</div>
</x-admin-layout>
