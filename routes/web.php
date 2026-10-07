<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminQuotationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BulkInventoryController;
use App\Http\Controllers\Admin\ContactAdminController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicOfferController;
use App\Http\Controllers\Admin\AdminAuditController;
use App\Services\DashboardMetricsService;
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
    // Perfil y Rastreo de Pedidos del Cliente
    Route::get('/perfil', [CustomerProfileController::class, 'profile'])->name('customer.profile');
    Route::patch('/perfil/datos', [CustomerProfileController::class, 'updateProfile'])->name('customer.profile.update');
    Route::patch('/perfil/clave', [CustomerProfileController::class, 'updatePassword'])->name('customer.password.update');
    Route::get('/mis-pedidos', [CustomerProfileController::class, 'orders'])->name('customer.orders');
    Route::get('/mis-pedidos/rastreo/{trackingCode}', [CustomerProfileController::class, 'trackOrder'])->name('customer.orders.track');
});

// Rutas de Acceso para Visitantes (No Autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

// Ruta de Cierre de Sesión Protegida
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Panel Administrativo - Dashboard Base
Route::get('/admin/dashboard', function (DashboardMetricsService $metricsService) {
    $metrics = $metricsService->getExecutiveSummary();
    return view('admin.dashboard', compact('metrics'));
})->name('admin.dashboard')->middleware(['auth', 'active', 'role:admin,inventory_manager']);

// Rutas Operativas: Gestor de Inventario y Administrador
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'role:inventory_manager,admin'])
    ->group(function () {
        // Catálogo Base y Promociones
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('products', AdminProductController::class);
        Route::resource('offers', OfferController::class);

        // Operaciones Masivas de Inventario
        Route::patch('/products/bulk-update', [BulkInventoryController::class, 'updateBulk'])->name('products.bulkUpdate');
        Route::delete('/products/bulk-delete', [BulkInventoryController::class, 'destroyBulk'])->name('products.bulkDelete');

        // Gestión de Pedidos
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

        // Auditoría de Cotizaciones del Bot
        Route::get('/quotations', [AdminQuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/{id}', [AdminQuotationController::class, 'show'])->name('quotations.show');
    });

// Rutas Exclusivas: Administrador
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'role:admin'])
    ->group(function () {
        // Mantenimiento de Usuarios y Permisos
        Route::resource('users', AdminUserController::class);

        // Bandeja de Entrada y Respuestas de Contacto
        Route::get('/messages', [ContactAdminController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [ContactAdminController::class, 'show'])->name('messages.show');
        Route::patch('/messages/{id}/status', [ContactAdminController::class, 'updateStatus'])->name('messages.updateStatus');
        // Bitácora de Auditoría y Trazabilidad Forense
        Route::get('/audits', [AdminAuditController::class, 'index'])->name('audits.index');
    });