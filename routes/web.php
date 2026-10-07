<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\PublicOfferController;
use Illuminate\Support\Facades\Route;

// Rutas base provisionales para redireccionamiento seguro del backend
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas Públicas de Catálogo, Ficha Técnica y Ofertas
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/ofertas', [PublicOfferController::class, 'index'])->name('offers.index');

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