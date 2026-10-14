<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

// --- Sitio público (visitantes) ---
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/nosotros', [PageController::class, 'about'])->name('about');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{product:sku}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/ofertas', [OfferController::class, 'index'])->name('offers.index');
Route::get('/contacto', [ContactController::class, 'create'])->name('contact');
Route::post('/contacto', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');

// Después de iniciar sesión: admin e inventario al panel, cliente al cotizador
Route::get('/dashboard', function () {
    return auth()->user()->canManageInventory()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('quote.index');
})->middleware('auth')->name('dashboard');

// --- Cotizador: clientes (y admin); el gestor de inventario no cotiza ---
Route::middleware(['auth', 'role:cliente,admin'])->group(function () {
    Route::get('/cotizador', [QuoteController::class, 'index'])->name('quote.index');
    Route::post('/cotizador/agregar/{product}', [QuoteController::class, 'add'])->name('quote.add');
    Route::patch('/cotizador/item/{item}', [QuoteController::class, 'update'])->name('quote.item.update');
    Route::delete('/cotizador/item/{item}', [QuoteController::class, 'remove'])->name('quote.item.remove');
    Route::post('/cotizador/enviar', [QuoteController::class, 'submit'])->name('quote.submit');
    // Asistente con IA: solo clientes
    Route::post('/cotizador/asistente', [QuoteController::class, 'assistant'])->name('quote.assistant')->middleware(['role:cliente', 'throttle:10,1']);
    Route::delete('/cotizador/asistente', [QuoteController::class, 'resetAssistant'])->name('quote.assistant.reset')->middleware('role:cliente');
    Route::get('/historial', [QuoteController::class, 'history'])->name('quote.history');
    Route::post('/cotizaciones/{quote}/pedido', [OrderController::class, 'fromQuote'])->name('quote.order');
});

// --- Usuarios con sesión ---
Route::middleware('auth')->group(function () {
    // Ver y descargar una cotización (el dueño, o el panel al revisarla)
    Route::get('/cotizaciones/{quote}', [QuoteController::class, 'show'])->name('quote.show');
    Route::get('/cotizaciones/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quote.pdf');

    // Carrito, compra y pedidos
    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/agregar/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/carrito/item/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrito/item/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:10,1');
    Route::get('/checkout/exito/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/mis-pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/mis-pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/mis-pedidos/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Panel administrativo: administrador y gestor de inventario ---
Route::middleware(['auth', 'role:admin,inventario'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    // La acción masiva va antes del resource para que "masivo" no se tome como {product}
    Route::patch('/productos/masivo', [Admin\ProductController::class, 'bulk'])->name('products.bulk');
    Route::resource('productos', Admin\ProductController::class)->parameters(['productos' => 'product'])->names('products')->except('show');
    Route::resource('categorias', Admin\CategoryController::class)->parameters(['categorias' => 'category'])->names('categories')->except('show');
    Route::resource('ofertas', Admin\OfferController::class)->parameters(['ofertas' => 'offer'])->names('offers')->except('show');
    Route::get('/inventario', [Admin\DashboardController::class, 'inventory'])->name('inventory');
    Route::get('/pedidos', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/pedidos/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::get('/cotizaciones', [Admin\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/cotizaciones/{quote}', [Admin\QuoteController::class, 'show'])->name('quotes.show');
    Route::patch('/cotizaciones/{quote}', [Admin\QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/cotizaciones/{quote}', [Admin\QuoteController::class, 'destroy'])->name('quotes.destroy');
});

// --- Panel administrativo: solo administrador ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/auditoria', [Admin\AuditController::class, 'index'])->name('audits.index');
    Route::get('/mensajes', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('/mensajes/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
    Route::delete('/mensajes/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/usuarios', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/usuarios/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
