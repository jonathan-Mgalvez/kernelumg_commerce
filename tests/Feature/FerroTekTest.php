<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FerroTekTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@ferrotek.test')->first();
    }

    private function cliente(): User
    {
        return User::where('email', 'cliente@ferrotek.test')->first();
    }

    public function test_paginas_publicas_cargan(): void
    {
        foreach (['/', '/nosotros', '/catalogo', '/catalogo?categoria=fijaciones&q=tornillo', '/contacto', '/catalogo/FT-ST-4410', '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_formulario_de_contacto_guarda_en_bd(): void
    {
        $this->post('/contacto', [
            'name' => 'Ana', 'email' => 'ana@example.com', 'subject' => 'Precio', 'message' => 'Hola',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', ['email' => 'ana@example.com']);
    }

    public function test_registro_crea_cliente_y_no_admin(): void
    {
        $this->post('/register', [
            'name' => 'Nuevo', 'email' => 'nuevo@example.com', 'role' => 'admin',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);

        $this->assertSame('cliente', User::where('email', 'nuevo@example.com')->value('role'));
    }

    public function test_cliente_no_puede_entrar_al_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs($this->cliente())->get('/admin')->assertForbidden();
    }

    public function test_admin_ve_todas_las_pantallas_del_panel(): void
    {
        $this->actingAs($this->admin());
        $quote = Quote::first();
        $message = ContactMessage::first();
        $product = Product::first();

        foreach (['/admin', '/admin/productos', '/admin/productos/create', "/admin/productos/{$product->id}/edit",
            '/admin/categorias', '/admin/categorias/create', '/admin/inventario', '/admin/cotizaciones',
            "/admin/cotizaciones/{$quote->id}", '/admin/mensajes', "/admin/mensajes/{$message->id}", '/admin/usuarios'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_crud_de_productos(): void
    {
        $this->actingAs($this->admin());
        $cat = Category::first();

        $this->post('/admin/productos', [
            'category_id' => $cat->id, 'sku' => 'FT-TEST-1', 'name' => 'Producto prueba',
            'price' => 10.5, 'unit' => 'unidad', 'stock' => 5, 'min_stock' => 1, 'active' => 1,
        ])->assertRedirect('/admin/productos');
        $p = Product::where('sku', 'FT-TEST-1')->firstOrFail();

        $this->put("/admin/productos/{$p->id}", [
            'category_id' => $cat->id, 'sku' => 'FT-TEST-1', 'name' => 'Producto editado',
            'price' => 20, 'unit' => 'caja', 'stock' => 9, 'min_stock' => 1,
        ])->assertRedirect('/admin/productos');
        $this->assertSame('Producto editado', $p->fresh()->name);
        $this->assertFalse($p->fresh()->active);

        $this->delete("/admin/productos/{$p->id}");
        $this->assertDatabaseMissing('products', ['sku' => 'FT-TEST-1']);
    }

    public function test_cotizador_calcula_descuento_flete_e_iva(): void
    {
        $user = $this->cliente();
        $this->actingAs($user)->get('/cotizador')->assertOk();

        $martillo = Product::where('sku', 'FT-TL-8802')->first(); // Q222.30
        $this->post("/cotizador/agregar/{$martillo->id}", ['quantity' => 2]);

        $quote = $user->quotes()->where('status', 'borrador')->first();
        // 2 x 222.30 = 444.60, sin descuento, flete 350 (menor a Q5,000), IVA 12% sobre 794.60
        $this->assertEquals(444.60, (float) $quote->subtotal);
        $this->assertEquals(0, (float) $quote->discount);
        $this->assertEquals(350, (float) $quote->shipping);
        $this->assertEquals(95.35, (float) $quote->tax);
        $this->assertEquals(889.95, (float) $quote->total);

        // 100 unidades => 5% de descuento por volumen y flete bonificado
        $item = $quote->items()->first();
        $this->patch("/cotizador/item/{$item->id}", ['quantity' => 100]);
        $quote->refresh();
        $this->assertEquals(1111.50, (float) $quote->discount);
        $this->assertEquals(0, (float) $quote->shipping);

        $this->post('/cotizador/enviar', ['delivery_address' => 'Obra X'])->assertRedirect("/cotizaciones/{$quote->id}");
        $this->assertSame('enviada', $quote->fresh()->status);

        $this->get("/cotizaciones/{$quote->id}/pdf")->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/historial')->assertOk()->assertSee($quote->code);
    }

    public function test_cliente_no_ve_cotizaciones_ajenas(): void
    {
        $otro = User::factory()->create();
        $quote = Quote::first();
        $this->actingAs($otro)->get("/cotizaciones/{$quote->id}")->assertForbidden();
    }
}
