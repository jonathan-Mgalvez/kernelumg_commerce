<?php

namespace App\Services;

use App\Mail\OrderConfirmedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class OrderProcessingService
{
    protected InventoryLockService $inventoryService;

    public function __construct(InventoryLockService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function processOrder(int $userId, array $checkoutData): Order
    {
        return DB::transaction(function () use ($userId, $checkoutData) {
            $cart = Cart::where('user_id', $userId)->with('items.product.activeOffer')->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['No existen productos dentro de la cesta de compras para procesar el pedido.']
                ]);
            }

            // 1. Bloqueo Pesimista (SELECT ... FOR UPDATE) y Validación de Existencias
            $lockedProducts = $this->inventoryService->lockAndValidate($cart->items);

            // 2. Cálculo Financiero Consolidado y Desglose
            $subtotalNeto = 0.00;
            $itemsToPersist = [];

            foreach ($cart->items as $cartItem) {
                $product = $lockedProducts[$cartItem->product_id];
                $basePrice = (float) $product->price;
                $discountApplied = 0.00;

                if ($product->activeOffer) {
                    $rate = (float) $product->activeOffer->discount_rate;
                    $discountApplied = round($basePrice * ($rate / 100), 2);
                }

                $effectiveUnitPrice = $basePrice - $discountApplied;
                $lineSubtotal = round($effectiveUnitPrice * $cartItem->quantity, 2);
                $totalDiscountLine = round($discountApplied * $cartItem->quantity, 2);

                $subtotalNeto += $lineSubtotal;

                $itemsToPersist[] = [
                    'product_id' => $product->id,
                    'unit_price' => $effectiveUnitPrice,
                    'quantity' => $cartItem->quantity,
                    'discount_applied' => $totalDiscountLine,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $taxCalculated = round($subtotalNeto * 0.12, 2);
            $totalFinal = round($subtotalNeto + $taxCalculated, 2);

            // 3. Generación de Código Único de Tracking
            $trackingCode = 'KUMG-' . strtoupper(Str::random(4)) . '-' . date('YmdHis');

            // 4. Creación del Registro Maestro del Pedido
            $order = Order::create([
                'user_id' => $userId,
                'tracking_code' => $trackingCode,
                'subtotal' => $subtotalNeto,
                'tax' => $taxCalculated,
                'total' => $totalFinal,
                'status' => 'Pendiente',
                'shipping_address' => $checkoutData['shipping_address'],
                'payment_method' => $checkoutData['payment_method'],
            ]);

            // 5. Inserción de Líneas de Detalle Congeladas
            foreach ($itemsToPersist as $itemData) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'discount_applied' => $itemData['discount_applied'],
                    'subtotal' => $itemData['subtotal'],
                ]);
            }

            // 6. Deducción Atómica de Existencias de Inventario
            $this->inventoryService->deductStock($cart->items, $lockedProducts);

            // 7. Vaciado del Carrito de Compras
            $cart->items()->delete();

            // 8. Despacho Asíncrono de Correo Transaccional de Confirmación
            try {
               Mail::to(Auth::user()->email)->send(new OrderConfirmedMail($order));
            } catch (\Exception $e) {
                logger()->error('Fallo de transporte SMTP al despachar confirmación de pedido: ' . $e->getMessage());
            }

            return $order;
        });
    }
}