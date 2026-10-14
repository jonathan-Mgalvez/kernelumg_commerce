<x-admin-layout title="Usuarios">
    <div class="card overflow-x-auto">
        <table class="table-ft min-w-[860px]">
            <thead><tr><th>Nombre</th><th>Empresa</th><th>Correo</th><th class="!text-right">Cotizaciones</th><th class="!text-right">Pedidos</th><th>Rol</th><th>Cuenta</th><th></th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    @php($yo = $u->is(auth()->user()))
                    <tr class="{{ $u->active ? '' : 'opacity-60' }}">
                        <td class="font-semibold">{{ $u->name }}</td>
                        <td>{{ $u->company ?? '—' }}</td>
                        <td class="text-muted">{{ $u->email }}</td>
                        <td class="num">{{ $u->quotes_count }}</td>
                        <td class="num">{{ $u->orders_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.update', $u) }}">
                                @csrf @method('PATCH')
                                <select name="role" class="input h-8 w-44 text-body-sm" onchange="this.form.submit()" @disabled($yo)>
                                    @foreach (\App\Models\User::ROLES as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected($u->role === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>
                            @if ($yo)
                                <span class="chip-ok">Activa</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.update', $u) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="active" value="{{ $u->active ? 0 : 1 }}">
                                    <button class="{{ $u->active ? 'chip-ok' : 'chip-danger' }} hover:opacity-80" title="{{ $u->active ? 'Clic para desactivar' : 'Clic para activar' }}">
                                        <span class="material-symbols-outlined text-[14px]">{{ $u->active ? 'toggle_on' : 'toggle_off' }}</span>
                                        {{ $u->active ? 'Activa' : 'Bloqueada' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="text-right">
                            @unless ($yo)
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('¿Eliminar a {{ $u->name }} y sus cotizaciones?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-danger btn-sm"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
    <p class="mt-3 text-body-sm text-muted">
        <strong>Gestor de inventario:</strong> productos, categorías, almacén, ofertas, pedidos y cotizaciones.
        <strong>Administrador:</strong> además usuarios, mensajes y auditoría.
    </p>
</x-admin-layout>
