<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/api/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/api/auth/status', [App\Http\Controllers\AuthController::class, 'status']);

Route::post('/api/favRoute/store', [App\Http\Controllers\FavRouteController::class, 'store']);
Route::get('/api/favRoute/read', [App\Http\Controllers\FavRouteController::class, 'read']);

Route::get('/api/lostItems', [App\Http\Controllers\LostItemController::class, 'list']);

Route::middleware(['auth'])->group(function () {
    Route::post('/api/lostItem', [App\Http\Controllers\LostItemController::class, 'create']);
    Route::get('/api/llostItem/{lostItemID}', [App\Http\Controllers\LostItemController::class, 'read']);
    Route::put('/api/lostItem/{lostItemID}', [App\Http\Controllers\LostItemController::class, 'update']);
    Route::delete('/api/lostItem/{lostItemID}', [App\Http\Controllers\LostItemController::class, 'delete']);
});
