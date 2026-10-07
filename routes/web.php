<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PublicOfferController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;


// Rutas Institucionales
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/quienes-somos', [PageController::class, 'about'])->name('about');

// Rutas Públicas de Catálogo, Ficha Técnica y Ofertas
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/ofertas', [PublicOfferController::class, 'index'])->name('offers.index');

// Rutas Públicas del Formulario de Contacto
Route::get('/contacto', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacto', [ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,1');

// Rutas Públicas e Híbridas del Carrito de Compras
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/agregar/{productId}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrito/actualizar/{itemId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrito/eliminar/{itemId}', [CartController::class, 'remove'])->name('cart.remove');

// Rutas Protegidas del Proceso de Checkout y Confirmación de Compra
Route::middleware(['auth', 'active', 'role:customer,inventory_manager,admin'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/procesar', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/exito/{tracking}', [CheckoutController::class, 'success'])->name('checkout.success');
});

// Panel Administrativo - Dashboard Base
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard')->middleware(['auth', 'active', 'role:admin,inventory_manager']);

// Rutas de Acceso para Visitantes (No Autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

// Ruta de Cierre de Sesión Protegida
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Panel Administrativo: Catálogo y Ofertas (Gestores de Inventario y Administradores)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'role:inventory_manager,admin'])
    ->group(function () {
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('products', AdminProductController::class);
        Route::resource('offers', OfferController::class);
    });
