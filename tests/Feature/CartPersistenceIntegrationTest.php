<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartPersistenceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migra_o_asigna_carrito_de_sesion_a_usuario_autenticado(): void
    {
        $role = Role::create([
            'name' => 'Cliente',
            'slug' => 'customer',
        ]);

        /** @var User $user */
        $user = User::create([
            'role_id' => $role->id,
            'name' => 'Cliente de Prueba',
            'email' => 'cliente.test@kernelumg.com',
            'password' => Hash::make('Password123#'),
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Componentes',
            'slug' => 'componentes',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'TEST-SKU-001',
            'name' => 'Memoria RAM DDR5 16GB',
            'slug' => 'memoria-ram-ddr5-16gb',
            'description' => 'Módulo de memoria RAM para pruebas',
            'price' => 250.00,
            'stock' => 15,
            'is_active' => true,
        ]);

        // Simular visitante agregando productos a su sesión
        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $guestCart = $cartService->getActiveCart();
        $this->assertNull($guestCart->user_id);
        $this->assertNotNull($guestCart->session_token);

        // Autenticar al usuario en el sistema
        $this->actingAs($user);

        // Invocar el carrito bajo el contexto del usuario autenticado
        $authCart = $cartService->getActiveCart();
        $this->assertEquals($user->id, $authCart->user_id);
    }
}