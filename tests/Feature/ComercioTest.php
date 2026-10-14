<?php

namespace Tests\Feature;

use App\Mail\PedidoNotificacion;
use App\Models\Audit;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ComercioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    private function usuario(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function producto(string $sku): Product
    {
        return Product::where('sku', $sku)->firstOrFail();
    }

    public function test_paginas_nuevas_cargan(): void
    {
        $this->get('/ofertas')->assertOk()->assertSee('Ofertas vigentes');

        $cliente = $this->usuario('cliente@ferrotek.test');
        $order = $cliente->orders()->first();
        $this->actingAs($cliente);
        foreach (['/carrito', '/checkout', '/mis-pedidos', "/mis-pedidos/{$order->id}", '/profile'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs($this->usuario('admin@ferrotek.test'));
        $offer = Offer::first();
        foreach (['/admin', '/admin/ofertas', '/admin/ofertas/create', "/admin/ofertas/{$offer->id}/edit",
            '/admin/pedidos', "/admin/pedidos/{$order->id}", '/admin/auditoria'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_carrito_limita_la_cantidad_al_stock(): void
    {
        $user = User::factory()->create();
        $arnes = $this->producto('FT-SG-0450'); // stock 4

        $this->actingAs($user)->post("/carrito/agregar/{$arnes->id}", ['quantity' => 10])
            ->assertSessionHas('status', 'Solo hay 4 en stock; se ajustó la cantidad.');
        $this->assertSame(4, $user->cart->items()->first()->quantity);

        $this->post("/carrito/agregar/{$arnes->id}")->assertSessionHasErrors('quantity');
    }

    public function test_checkout_crea_pedido_descuenta_stock_y_vacia_carrito(): void
    {
        $user = User::factory()->create();
        $martillo = $this->producto('FT-TL-8802'); // Q222.30, con oferta del 15 % en el seeder
        $stock = $martillo->stock;

        $this->actingAs($user)->post("/carrito/agregar/{$martillo->id}", ['quantity' => 2]);
        $this->post('/checkout', [
            'payment_method' => 'transferencia', 'shipping_address' => 'Obra Z', 'notes' => null,
        ])->assertRedirect();

        $order = $user->orders()->with('items')->firstOrFail();
        // 2 x 222.30 = 444.60; oferta 15 % = 66.69; neto 377.91 + flete 350 = 727.91; IVA 87.35
        $this->assertEquals(444.60, (float) $order->subtotal);
        $this->assertEquals(66.69, (float) $order->discount);
        $this->assertEquals(350, (float) $order->shipping);
        $this->assertEquals(815.26, (float) $order->total);
        $this->assertSame('pendiente', $order->status);
        $this->assertSame($stock - 2, $martillo->fresh()->stock);
        $this->assertSame(0, $user->cart->items()->count());
        Mail::assertSent(PedidoNotificacion::class, fn ($m) => $m->order->is($order) && $m->tipo === 'confirmado');

        $this->get("/checkout/exito/{$order->id}")->assertOk()->assertSee($order->code);
    }

    public function test_stock_insuficiente_no_deja_cambios(): void
    {
        $user = User::factory()->create();
        $arnes = $this->producto('FT-SG-0450');

        $this->actingAs($user)->post("/carrito/agregar/{$arnes->id}", ['quantity' => 4]);
        $arnes->update(['stock' => 1]); // otro cliente compró mientras tanto

        $this->post('/checkout', ['payment_method' => 'deposito', 'shipping_address' => 'X'])->assertSessionHasErrors('stock');
        $this->assertSame(0, $user->orders()->count());
        $this->assertSame(1, $arnes->fresh()->stock);
        $this->assertSame(1, $user->cart->items()->count());
    }

    public function test_cancelar_devuelve_stock_y_no_se_reactiva(): void
    {
        $cliente = $this->usuario('cliente@ferrotek.test');
        $order = $cliente->orders()->where('status', 'pendiente')->with('items')->firstOrFail();
        $item = $order->items->first();
        $stock = $item->product->stock;

        $this->actingAs($cliente)->patch("/mis-pedidos/{$order->id}/cancelar")->assertSessionHas('status');
        $this->assertSame('cancelado', $order->fresh()->status);
        $this->assertSame($stock + $item->quantity, $item->product->fresh()->stock);

        $this->actingAs($this->usuario('admin@ferrotek.test'))
            ->patch("/admin/pedidos/{$order->id}", ['status' => 'procesando'])->assertSessionHasErrors('status');
        $this->assertSame('cancelado', $order->fresh()->status);
    }

    public function test_cliente_no_ve_pedidos_ajenos(): void
    {
        $order = Order::first();
        $this->actingAs(User::factory()->create())->get("/mis-pedidos/{$order->id}")->assertForbidden();
    }

    public function test_cotizacion_aprobada_se_convierte_en_pedido_con_sus_precios(): void
    {
        $cliente = $this->usuario('cliente@ferrotek.test');
        $quote = $cliente->quotes()->where('status', 'enviada')->firstOrFail();
        $quote->update(['status' => 'aprobada', 'valid_until' => now()->addDays(5)]);

        $this->actingAs($cliente)->post("/cotizaciones/{$quote->id}/pedido", [
            'payment_method' => 'transferencia', 'shipping_address' => 'Obra Torre Norte',
        ])->assertRedirect();

        $order = $quote->fresh()->order;
        $this->assertNotNull($order);
        $this->assertEquals((float) $quote->total, (float) $order->total);
        $this->assertSame($quote->items()->count(), $order->items()->count());

        // No se puede convertir dos veces
        $this->post("/cotizaciones/{$quote->id}/pedido", ['payment_method' => 'deposito', 'shipping_address' => 'X'])
            ->assertSessionHasErrors('quote');
    }

    public function test_rol_inventario_gestiona_productos_pero_no_usuarios_ni_auditoria(): void
    {
        $inv = $this->usuario('inventario@ferrotek.test');
        $this->actingAs($inv);

        foreach (['/admin', '/admin/productos', '/admin/productos/create', '/admin/ofertas', '/admin/pedidos', '/admin/inventario'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/admin/usuarios', '/admin/auditoria', '/admin/mensajes'] as $url) {
            $this->get($url)->assertForbidden();
        }

        // Crea un producto desde la página: se guarda en BD y aparece en el catálogo
        $this->post('/admin/productos', [
            'category_id' => Category::first()->id, 'sku' => 'FT-NEW-001', 'name' => 'Pala Cuadrada Truper',
            'price' => 95, 'unit' => 'unidad', 'stock' => 30, 'min_stock' => 5, 'active' => 1,
        ])->assertRedirect('/admin/productos');
        $this->get('/catalogo?q=Pala')->assertSee('Pala Cuadrada Truper');

        $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_asistente_ia_solo_para_clientes(): void
    {
        config(['services.gemini.key' => 'clave-de-prueba']);

        $this->actingAs($this->usuario('cliente@ferrotek.test'))->get('/cotizador')->assertOk()->assertSee('x-data="asistente(', false);

        $this->actingAs($this->usuario('admin@ferrotek.test'));
        $this->get('/cotizador')->assertOk()->assertDontSee('x-data="asistente(', false);
        $this->postJson('/cotizador/asistente', ['mensaje' => 'hola'])->assertForbidden();
    }

    public function test_inventario_no_cotiza(): void
    {
        $inv = $this->usuario('inventario@ferrotek.test');
        $martillo = $this->producto('FT-TL-8802');
        $this->actingAs($inv);

        $this->get('/cotizador')->assertForbidden();
        $this->get('/historial')->assertForbidden();
        $this->post("/cotizador/agregar/{$martillo->id}")->assertForbidden();
        $this->postJson('/cotizador/asistente', ['mensaje' => 'hola'])->assertForbidden();
        $this->assertSame(0, $inv->quotes()->count());

        // Sin botones ni enlaces de cotizar en el sitio
        foreach (['/', '/catalogo', '/catalogo/FT-TL-8802', '/profile'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee(route('quote.index'), false)
                ->assertDontSee(route('quote.add', $martillo), false);
        }

        // Sigue pudiendo revisar cotizaciones de clientes desde el panel
        $this->get('/admin/cotizaciones')->assertOk();
    }

    public function test_cambios_quedan_en_auditoria(): void
    {
        $admin = $this->usuario('admin@ferrotek.test');
        $p = $this->producto('FT-TL-8802');

        $this->actingAs($admin)->put("/admin/productos/{$p->id}", [
            'category_id' => $p->category_id, 'sku' => $p->sku, 'name' => $p->name,
            'price' => 250, 'unit' => $p->unit, 'stock' => $p->stock, 'min_stock' => $p->min_stock, 'active' => 1,
        ]);

        $audit = Audit::where('auditable_type', Product::class)->where('auditable_id', $p->id)->latest('id')->firstOrFail();
        $this->assertSame('actualizado', $audit->action);
        $this->assertSame($admin->id, $audit->user_id);
        $this->assertEquals(250, $audit->new_values['price']);
        $this->assertArrayNotHasKey('updated_at', $audit->new_values);

        $this->get('/admin/auditoria')->assertOk()->assertSee('Producto #'.$p->id);
    }

    public function test_usuario_desactivado_no_puede_entrar(): void
    {
        $cliente = $this->usuario('cliente@ferrotek.test');
        $cliente->forceFill(['active' => false])->save();

        $this->post('/login', ['email' => $cliente->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        // Si ya tenía sesión abierta, se cierra en la siguiente petición
        $this->actingAs($cliente)->get('/cotizador')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_acciones_masivas_de_productos(): void
    {
        $ids = Product::whereIn('sku', ['FT-TL-8802', 'FT-AB-3341'])->pluck('price', 'id');

        $this->actingAs($this->usuario('admin@ferrotek.test'))
            ->patch('/admin/productos/masivo', ['ids' => $ids->keys()->all(), 'accion' => 'precio', 'valor' => 10])
            ->assertSessionHas('status');

        foreach ($ids as $id => $precio) {
            $this->assertEquals(round($precio * 1.1, 2), (float) Product::find($id)->price);
        }

        $this->patch('/admin/productos/masivo', ['ids' => $ids->keys()->all(), 'accion' => 'desactivar']);
        $this->assertSame(0, Product::whereIn('id', $ids->keys())->where('active', true)->count());
    }

    public function test_comando_purgar_vence_cotizaciones(): void
    {
        $quote = Quote::where('status', 'enviada')->firstOrFail();
        $quote->update(['valid_until' => now()->subDays(2)]);

        $this->artisan('ferrotek:purgar')->assertSuccessful();
        $this->assertSame('vencida', $quote->fresh()->status);
    }
}
