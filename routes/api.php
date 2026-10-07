<?php

use App\Http\Controllers\Api\BotApiController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Endpoint ligero para poblar el selector de cotización del asistente virtual
Route::get('/products-catalog', function () {
    return Product::where('is_active', true)
        ->select('id', 'name', 'sku', 'price')
        ->orderBy('name')
        ->get();
});

Route::prefix('bot')->group(function () {
    Route::post('/faq', [BotApiController::class, 'faq'])->middleware('throttle:30,1');
    Route::post('/quote/calculate', [BotApiController::class, 'calculate'])->middleware('throttle:20,1');
    Route::post('/quote/convert-to-cart', [BotApiController::class, 'convertToCart'])->middleware('throttle:15,1');
});