<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\OrderProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ConcurrencyCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_evita_sobreventa_cuando_stock_es_insuficiente(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'customer']);

        /** @var User $userA */
        $userA = User::create([
            'role_id' => $role->id,
            'name' => 'Usuario A',
            'email' => 'usuarioA@test.com',
            'password' => Hash::make('Secret123#'),
            'is_active' => true,
        ]);

        /** @var User $userB */
        $userB = User::create([
            'role_id' => $role->id,
            'name' => 'Usuario B',
            'email' => 'usuarioB@test.com',
            'password' => Hash::make('Secret123#'),
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Monitores',
            'slug' => 'monitores',
            'is_active' => true,
        ]);

        // Solo 1 unidad disponible en inventario
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'MON-TEST-500',
            'name' => 'Monitor Gamer 144Hz',
            'slug' => 'monitor-gamer-144hz',
            'description' => 'Monitor con inventario crítico de 1 unidad',
            'stock' => 1,
            'price' => 500.00,
            'is_active' => true,
        ]);

        // Carrito para Usuario A
        $cartA = Cart::create(['user_id' => $userA->id]);
        CartItem::create(['cart_id' => $cartA->id, 'product_id' => $product->id, 'quantity' => 1]);

        // Carrito para Usuario B
        $cartB = Cart::create(['user_id' => $userB->id]);
        CartItem::create(['cart_id' => $cartB->id, 'product_id' => $product->id, 'quantity' => 1]);

        $orderService = app(OrderProcessingService::class);

        // Compra Usuario A (debe completarse con éxito)
        $orderA = $orderService->processOrder($userA->id, [
            'shipping_address' => 'Ciudad de Guatemala',
            'payment_method' => 'Pago Contra Entrega',
        ]);

        $this->assertNotNull($orderA);
        $this->assertEquals(0, $product->fresh()->stock);

        // Compra Usuario B (debe abortar por falta de inventario)
        $this->expectException(ValidationException::class);
        $orderService->processOrder($userB->id, [
            'shipping_address' => 'Quetzaltenango',
            'payment_method' => 'Transferencia Bancaria',
        ]);
    }
}