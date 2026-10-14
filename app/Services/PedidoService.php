<?php

namespace App\Services;

use App\Mail\PedidoNotificacion;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Convierte el carrito (o una cotización aprobada) en pedido.
 * Todo ocurre en una transacción con los productos bloqueados (SELECT ... FOR UPDATE),
 * así dos clientes comprando al mismo tiempo no pueden vender más stock del que existe.
 */
class PedidoService
{
    public function __construct(private CarritoService $carrito) {}

    /** @param array{payment_method:string, shipping_address:string, notes?:?string} $datos */
    public function desdeCarrito(User $user, array $datos): Order
    {
        $order = DB::transaction(function () use ($user, $datos) {
            $cart = $this->carrito->carrito($user);
            $items = $cart->items()->with('product')->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Tu carrito está vacío.']);
            }

            $productos = $this->bloquear($items);
            $productos->load('activeOffer');
            $items->each(fn ($item) => $item->setRelation('product', $productos[$item->product_id]));

            $order = $this->crear($user, $datos, $this->carrito->calcular($items), $productos);
            $cart->items()->delete();

            return $order;
        });

        $this->notificar($order, 'confirmado');

        return $order;
    }

    /** Pedido con los precios y descuentos congelados en la cotización aprobada. */
    public function desdeCotizacion(Quote $quote, array $datos): Order
    {
        $order = DB::transaction(function () use ($quote, $datos) {
            $quote = Quote::whereKey($quote->id)->lockForUpdate()->firstOrFail();
            if (! $quote->puedeConvertirse()) {
                throw ValidationException::withMessages(['quote' => 'Esta cotización ya no se puede convertir en pedido (debe estar aprobada, vigente y sin pedido previo).']);
            }

            $items = $quote->items()->with('product')->get();
            $productos = $this->bloquear($items);

            $totales = [
                'lineas' => $items->map(fn ($i) => [
                    'product_id' => $i->product_id,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'discount_rate' => $i->discount_rate,
                    'amount' => $i->amount,
                ])->all(),
                'subtotal' => $quote->subtotal,
                'discount' => $quote->discount,
                'shipping' => $quote->shipping,
                'tax' => $quote->tax,
                'total' => $quote->total,
            ];

            return $this->crear($quote->user, $datos, $totales, $productos, $quote);
        });

        $this->notificar($order, 'confirmado');

        return $order;
    }

    /** Cambia el estado; al cancelar se devuelve el stock. Un pedido cancelado ya no cambia. */
    public function cambiarEstado(Order $order, string $estado): void
    {
        if ($order->status === $estado) {
            return;
        }
        if ($order->status === 'cancelado') {
            throw ValidationException::withMessages(['status' => 'Un pedido cancelado no se puede reactivar; el cliente debe hacer uno nuevo.']);
        }

        DB::transaction(function () use ($order, $estado) {
            if ($estado === 'cancelado') {
                foreach ($order->items as $item) {
                    Product::whereKey($item->product_id)->lockForUpdate()->first()?->increment('stock', $item->quantity);
                }
            }
            $order->update(['status' => $estado]);
        });

        $this->notificar($order, 'actualizado');
    }

    /** Bloquea los productos y valida que estén activos y con stock suficiente. */
    private function bloquear(Collection $items): Collection
    {
        $productos = Product::whereIn('id', $items->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');

        foreach ($items as $item) {
            $producto = $productos->get($item->product_id);
            if (! $producto || ! $producto->active) {
                throw ValidationException::withMessages(['stock' => "\"{$item->product?->name}\" ya no está disponible. Quítalo para continuar."]);
            }
            if ($producto->stock < $item->quantity) {
                throw ValidationException::withMessages(['stock' => "Stock insuficiente de {$producto->name}: pediste {$item->quantity} y hay {$producto->stock}."]);
            }
        }

        return $productos;
    }

    private function crear(User $user, array $datos, array $totales, Collection $productos, ?Quote $quote = null): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'quote_id' => $quote?->id,
            'code' => $this->nuevoCodigo(),
            'status' => 'pendiente',
            'payment_method' => $datos['payment_method'],
            'shipping_address' => $datos['shipping_address'],
            'notes' => $datos['notes'] ?? null,
            'subtotal' => $totales['subtotal'],
            'discount' => $totales['discount'],
            'shipping' => $totales['shipping'],
            'tax' => $totales['tax'],
            'total' => $totales['total'],
        ]);

        foreach ($totales['lineas'] as $linea) {
            $order->items()->create($linea);
            $productos[$linea['product_id']]->decrement('stock', $linea['quantity']);
        }

        return $order;
    }

    private function nuevoCodigo(): string
    {
        $anio = now()->year;
        $ultimo = Order::where('code', 'like', "PED-$anio-%")->lockForUpdate()->max('code');
        $n = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return sprintf('PED-%d-%04d', $anio, $n);
    }

    /** El correo nunca debe romper la compra: si falla, solo se registra en el log. */
    private function notificar(Order $order, string $tipo): void
    {
        try {
            Mail::to($order->user)->send(new PedidoNotificacion($order->fresh(['items.product', 'user']), $tipo));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
