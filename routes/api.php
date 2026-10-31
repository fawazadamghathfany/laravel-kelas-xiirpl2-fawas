<?php

use App\Http\Controllers\Api\MovieController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/movie', MovieController::class)->only(['index', 'show']);
Route::middleware('auth:sanctum')
    ->apiResource('/movie', MovieController::class)
    ->only(['store', 'update', 'destroy']);
