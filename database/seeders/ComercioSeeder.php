<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Datos de prueba del módulo de comercio: usuario de inventario, ofertas vigentes y pedidos.
 *
 *   php artisan db:seed --class=ComercioSeeder
 */
class ComercioSeeder extends Seeder
{
    public function run(): void
    {
        config(['mail.default' => 'array']); // no enviar correos de los pedidos de ejemplo

        User::firstOrCreate(['email' => 'inventario@ferrotek.test'], [
            'name' => 'Gestor de Inventario', 'password' => 'password', 'phone' => '5555-9876',
        ])->forceFill(['role' => User::ROLE_INVENTARIO])->save();

        // Ofertas: [sku, %, título, días desde hoy (inicio), días desde hoy (fin)]
        $ofertas = [
            ['FT-TL-8802', 15, 'Semana de la herramienta', -3, 10],
            ['FT-AB-3341', 20, 'Liquidación de discos', -1, 20],
            ['FT-PW-7019', 10, 'Taladro industrial en oferta', -5, 6],
            ['FT-SG-0330', 25, 'Mes de la seguridad', -2, 25],
            ['FT-FX-0220', 12, null, 0, 14],
            ['FT-WD-1022', 8, 'Soldadura E6011', -10, 3],
            ['FT-TL-8120', 18, 'Próxima promoción', 5, 30],   // programada
            ['FT-ST-4420', 10, 'Promoción pasada', -40, -10],  // vencida
        ];
        foreach ($ofertas as [$sku, $pct, $titulo, $desde, $hasta]) {
            if ($p = Product::where('sku', $sku)->first()) {
                Offer::create([
                    'product_id' => $p->id, 'title' => $titulo, 'discount_rate' => $pct,
                    'starts_at' => now()->addDays($desde)->startOfDay(), 'ends_at' => now()->addDays($hasta)->endOfDay(),
                    'active' => true,
                ]);
            }
        }

        // Pedidos: [email, días atrás, estado, pago, [[sku, cantidad]...]]
        $pedidos = [
            ['cliente@ferrotek.test', 1, 'pendiente', 'transferencia', [['FT-TL-8802', 3], ['FT-AB-3341', 40]]],
            ['ana.morales@constructoraandina.test', 2, 'procesando', 'deposito', [['FT-ST-4410', 80], ['FT-FX-0914', 500]]],
            ['cperez@taller-perez.test', 4, 'despachado', 'contra_entrega', [['FT-WD-1022', 5], ['FT-SG-0330', 20]]],
            ['mjcastillo@edificaciones.test', 7, 'entregado', 'transferencia', [['FT-SG-0210', 10], ['FT-FX-0220', 150]]],
            ['jorge.h@metalurgica.test', 12, 'entregado', 'deposito', [['FT-AB-3341', 100], ['FT-TL-8802', 6]]],
            ['sofia.ramirez@gmail.test', 15, 'cancelado', 'contra_entrega', [['FT-SG-0330', 4]]],
            ['lfgomez@ingenieria-lf.test', 20, 'entregado', 'transferencia', [['FT-ST-4420', 120], ['FT-FX-0914', 300]]],
        ];

        $carrito = app(CarritoService::class);
        $servicio = app(PedidoService::class);
        $direcciones = ['Obra Residencial Las Palmas, Zona 16', 'Taller central, Villa Nueva', 'Bodega Mixco, Km 15 Carretera Interamericana'];

        foreach ($pedidos as $i => [$email, $dias, $estado, $pago, $lineas]) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }
            foreach ($lineas as [$sku, $cantidad]) {
                $carrito->agregar($user, Product::where('sku', $sku)->firstOrFail(), $cantidad);
            }
            $order = $servicio->desdeCarrito($user, [
                'payment_method' => $pago,
                'shipping_address' => $direcciones[$i % count($direcciones)],
                'notes' => $i % 3 === 0 ? 'Llamar antes de entregar.' : null,
            ]);
            if ($estado === 'cancelado') {
                $servicio->cambiarEstado($order, 'cancelado');
            }
            $fecha = Carbon::now()->subDays($dias)->setTime(9 + $i, 20);
            $order->forceFill(['status' => $estado, 'created_at' => $fecha, 'updated_at' => $fecha->copy()->addHours(5)])->save();
        }

        // Una cotización aprobada convertida en pedido
        $aprobada = Quote::where('status', 'aprobada')->whereDate('valid_until', '>=', today())->latest()->get()
            ->first(fn (Quote $q) => $q->puedeConvertirse());
        if ($aprobada) {
            $servicio->desdeCotizacion($aprobada, [
                'payment_method' => 'transferencia',
                'shipping_address' => $aprobada->delivery_address ?? 'Obra del cliente',
            ])->forceFill(['status' => 'procesando'])->save();
        }

        // Un carrito con productos para el cliente de prueba
        $cliente = User::where('email', 'cliente@ferrotek.test')->first();
        $carrito->agregar($cliente, Product::where('sku', 'FT-SG-0330')->first(), 10);
        $carrito->agregar($cliente, Product::where('sku', 'FT-FX-0220')->first(), 50);
    }
}
