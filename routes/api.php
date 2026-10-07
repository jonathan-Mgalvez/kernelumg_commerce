<?php

use App\Http\Controllers\Api\BotApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('bot')->group(function () {
    Route::post('/faq', [BotApiController::class, 'faq'])->middleware('throttle:30,1');
    Route::post('/quote/calculate', [BotApiController::class, 'calculate'])->middleware('throttle:20,1');
    Route::post('/quote/convert-to-cart', [BotApiController::class, 'convertToCart'])->middleware('throttle:15,1');
});