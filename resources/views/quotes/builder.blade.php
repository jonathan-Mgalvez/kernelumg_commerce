<x-app-layout>
    <x-slot:title>Cotizador</x-slot:title>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-headline-lg text-primary">Asistente de Cotización</h1>
            @if ($asistenteActivo)
                <span class="chip-info"><span class="w-1.5 h-1.5 bg-primary-container"></span> Motor IA activo</span>
            @endif
            <span class="chip"><span class="w-1.5 h-1.5 bg-ok"></span> Sincronizado con almacén central</span>
        </div>
        <p class="mt-1 text-body-md text-on-surface-variant">
            Busca materiales, ajusta cantidades y obtén tu total con descuentos por volumen e IVA al instante.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 grid gap-6 lg:grid-cols-5">
        <!-- Columna izquierda: búsqueda y agregado de productos (reemplaza al chat de IA por ahora) -->
        <section class="lg:col-span-3 space-y-4">
            <div class="flex items-center justify-between text-label-md">
                <span class="flex items-center gap-1.5 uppercase text-muted">
                    <span class="material-symbols-outlined text-[18px]">forum</span> Sesión de cotización: {{ $quote->code }}
                </span>
                <span class="hidden sm:block text-on-surface-variant">Modo: Catálogo y stock en tiempo real</span>
            </div>

            @if ($asistenteActivo)
                <!-- Asistente con IA (Gemini + Function Calling) -->
                <div class="card" x-data="asistente(@js($chat))">
                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-line-soft">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 flex items-center justify-center rounded bg-primary-container text-white">
                                <span class="material-symbols-outlined text-[20px]">smart_toy</span>
                            </span>
                            <div>
                                <p class="text-label-lg font-semibold text-on-surface">FerroTek AI Assistant</p>
                                <p class="text-body-sm text-muted">Escribe lo que necesitas y lo agrego a tu cotización</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('quote.assistant.reset') }}" x-show="mensajes.length">
                            @csrf @method('DELETE')
                            <button class="text-label-sm uppercase text-muted hover:text-on-surface">Limpiar chat</button>
                        </form>
                    </div>

                    <div x-ref="lista" class="max-h-[420px] overflow-y-auto p-4 space-y-4 bg-surface-container-low">
                        <template x-if="!mensajes.length">
                            <div class="text-body-md text-on-surface-variant">
                                <p>Hola, {{ explode(' ', auth()->user()->name)[0] }}. Puedo buscar materiales, revisar existencias y armar tu cotización. Por ejemplo:</p>
                                <p class="mt-1 italic">"Necesito 500 varillas de 1/2 y 30 cajas de electrodo E6011 para el jueves."</p>
                            </div>
                        </template>
                        <template x-for="(m, i) in mensajes" :key="i">
                            <div class="flex gap-3" :class="m.rol === 'cliente' ? 'flex-row-reverse' : ''">
                                <span class="w-8 h-8 shrink-0 flex items-center justify-center rounded"
                                      :class="m.rol === 'cliente' ? 'bg-line-soft text-on-surface-variant' : 'bg-primary-container text-white'">
                                    <span class="material-symbols-outlined text-[18px]" x-text="m.rol === 'cliente' ? 'person' : 'smart_toy'"></span>
                                </span>
                                <div class="max-w-[85%]">
                                    <p class="text-label-sm text-muted mb-1" :class="m.rol === 'cliente' ? 'text-right' : ''"
                                       x-text="(m.rol === 'cliente' ? 'Tú' : 'FerroTek AI Assistant') + ' · ' + m.hora"></p>
                                    <div class="px-3 py-2 rounded border text-body-md whitespace-pre-line"
                                         :class="m.rol === 'cliente' ? 'bg-white border-line' : (m.error ? 'bg-danger/10 border-danger/30 text-danger' : 'bg-white border-line-soft')"
                                         x-text="limpiar(m.texto)"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="cargando" class="flex items-center gap-2 text-body-md text-muted">
                            <span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                            Consultando inventario y armando la cotización...
                        </div>
                    </div>

                    <div class="p-3 border-t border-line-soft">
                        <div class="flex flex-wrap gap-2 mb-2 text-label-md">
                            <template x-for="s in sugerencias">
                                <button type="button" @click="enviar(s)" :disabled="cargando"
                                        class="px-2.5 py-1 rounded bg-line-soft hover:bg-surface-container-high disabled:opacity-50" x-text="s"></button>
                            </template>
                        </div>
                        <form @submit.prevent="enviar()" class="flex gap-2">
                            <input x-model="texto" x-ref="input" maxlength="1000" :disabled="cargando" class="input"
                                   placeholder="Escribe una instrucción o añade materiales...">
                            <button class="btn-primary" :disabled="cargando || !texto.trim()">
                                Enviar <span class="material-symbols-outlined text-[18px]">send</span>
                            </button>
                        </form>
                    </div>
                </div>

                <p class="overline pt-2">O agrega productos manualmente</p>
            @endif

            <form method="GET" class="card p-3 flex gap-2">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[20px] text-muted">search</span>
                    <input name="q" value="{{ request('q') }}" placeholder="Busca materiales por nombre o SKU (ej. varilla, E6011, FT-ST)..." class="input pl-9">
                </div>
                <button class="btn-primary">Buscar</button>
            </form>

            <div class="card divide-y divide-line-soft">
                @forelse ($products as $product)
                    <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="w-16 h-16 shrink-0 bg-surface-container rounded overflow-hidden flex items-center justify-center">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-outline">{{ $product->category->icon }}</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-label-lg font-semibold text-on-surface">{{ $product->name }}</p>
                            <p class="text-body-sm text-muted">{{ $product->sku }} · {{ $product->standard }}</p>
                            <p class="mt-1 flex items-center gap-1 text-body-sm {{ $product->stock_status === 'ok' ? 'text-ok' : ($product->stock_status === 'bajo' ? 'text-warn' : 'text-danger') }}">
                                <span class="material-symbols-outlined text-[16px]">{{ $product->stock_status === 'agotado' ? 'cancel' : 'check_circle' }}</span>
                                {{ $product->stock_status === 'agotado' ? 'Sin existencias' : 'Disponible: '.number_format($product->stock).' · '.($product->location ?? 'Almacén Central') }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-label-lg font-semibold text-primary">Q{{ number_format($product->price, 2) }}</p>
                            <p class="text-body-sm text-muted">/ {{ $product->unit }}</p>
                        </div>
                        <form method="POST" action="{{ route('quote.add', $product) }}" class="flex gap-1.5 shrink-0">
                            @csrf
                            <input type="number" name="quantity" value="1" min="1" max="100000" class="input w-20" aria-label="Cantidad">
                            <button class="btn-primary px-3" title="Agregar" @disabled($product->stock <= 0)>
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="p-8 text-center text-muted">No hay productos que coincidan con "{{ request('q') }}".</p>
                @endforelse
            </div>
            {{ $products->links() }}

            <div class="flex flex-wrap items-center gap-2 text-label-md">
                <span class="uppercase text-muted">Sugerencias rápidas:</span>
                @foreach (['varilla', 'electrodo', 'disco', 'tornillo'] as $s)
                    <a href="{{ route('quote.index', ['q' => $s]) }}" class="px-3 py-1 rounded bg-line-soft hover:bg-surface-container-high">{{ ucfirst($s) }}</a>
                @endforeach
            </div>
        </section>

        <!-- Columna derecha: resumen -->
        <aside class="lg:col-span-2 space-y-4">
            <div id="resumen-cotizacion">
                @include('quotes._summary', ['quote' => $quote, 'editable' => true])
            </div>

            @if ($quote->items->isNotEmpty())
                <form method="POST" action="{{ route('quote.submit') }}" class="card p-4 space-y-3" id="form-enviar">
                    @csrf
                    <div>
                        <label for="delivery_address" class="label flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">local_shipping</span> Dirección de despacho *</label>
                        <input id="delivery_address" name="delivery_address" class="input" required maxlength="255"
                               value="{{ old('delivery_address', $quote->delivery_address) }}" placeholder="Obra Torre Norte, Av. Industrial 402">
                    </div>
                    <div>
                        <label for="notes" class="label">Notas (opcional)</label>
                        <textarea id="notes" name="notes" class="input h-20 py-2" maxlength="1000" placeholder="Horario de entrega, contacto en obra...">{{ old('notes', $quote->notes) }}</textarea>
                    </div>
                    <button class="btn-primary w-full h-11 uppercase">
                        <span class="material-symbols-outlined text-[20px]">send</span> Enviar cotización
                    </button>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('quote.pdf', $quote) }}" class="btn-secondary">
                            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> PDF
                        </a>
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-secondary">
                            <span class="material-symbols-outlined text-[18px]">chat</span> WhatsApp
                        </a>
                    </div>
                    <p class="flex items-center gap-1.5 text-body-sm text-muted">
                        <span class="material-symbols-outlined text-[16px]">verified_user</span>
                        Precios válidos por {{ config('empresa.vigencia_dias') }} días. Tu borrador se guarda automáticamente.
                    </p>
                </form>
            @endif
        </aside>
    </div>

    @if ($asistenteActivo)
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('asistente', (mensajesIniciales) => ({
                    mensajes: mensajesIniciales,
                    texto: '',
                    cargando: false,
                    sugerencias: ['500 varillas de 1/2"', 'Kit de seguridad para 6 trabajadores', '¿Qué electrodos tienen?', 'Quita el último producto'],

                    init() { this.bajar(); },

                    // El modelo a veces usa **negritas** de markdown; se muestran como texto plano.
                    limpiar(t) { return String(t).replace(/\*\*(.+?)\*\*/g, '$1').replace(/^\s*[\*-]\s+/gm, '• '); },

                    bajar() { this.$nextTick(() => { this.$refs.lista.scrollTop = this.$refs.lista.scrollHeight; }); },

                    async enviar(sugerencia = null) {
                        const mensaje = (sugerencia ?? this.texto).trim();
                        if (!mensaje || this.cargando) return;
                        const hora = new Date().toLocaleTimeString('es-GT', { hour: '2-digit', minute: '2-digit', hour12: false });
                        this.mensajes.push({ rol: 'cliente', texto: mensaje, hora });
                        this.texto = '';
                        this.cargando = true;
                        this.bajar();

                        try {
                            const res = await fetch(@js(route('quote.assistant')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                },
                                body: JSON.stringify({ mensaje }),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                const error = res.status === 429 && !data.error
                                    ? 'Enviaste muchos mensajes seguidos. Espera un minuto.'
                                    : (data.error || data.message || 'Ocurrió un error. Intenta de nuevo.');
                                this.mensajes.push({ rol: 'asistente', texto: error, hora, error: true });
                                return;
                            }

                            this.mensajes.push({ rol: 'asistente', texto: data.mensaje, hora: data.hora });
                            document.getElementById('resumen-cotizacion').innerHTML = data.resumen_html;
                            // La primera vez que la cotización tiene productos aparece el formulario de envío: recargar.
                            if (data.cotizacion.partidas.length && !document.getElementById('form-enviar')) {
                                window.location.reload();
                            }
                        } catch (e) {
                            this.mensajes.push({ rol: 'asistente', texto: 'No se pudo conectar con el servidor.', hora, error: true });
                        } finally {
                            this.cargando = false;
                            this.bajar();
                            this.$nextTick(() => this.$refs.input.focus());
                        }
                    },
                }));
            });
        </script>
    @endif
</x-app-layout>
