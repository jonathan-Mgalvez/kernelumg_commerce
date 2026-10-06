<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas base provisionales para redireccionamiento seguro del backend
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard')->middleware(['auth', 'role:admin,inventory_manager']);

// Rutas de Acceso para Visitantes (No Autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

// Ruta de Cierre de Sesión Protegida
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');