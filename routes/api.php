<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShipOrderController;

Route::group(['prefix' => 'orders'], function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::delete('/{order}', [OrderController::class, 'destroy']);
    Route::post('/ship', ShipOrderController::class);
});

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
});
