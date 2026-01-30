<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
});

// Rutas de autenticación para residentes
Route::prefix('residente')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\ResidenteAuthController::class, 'login']);
    
    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\ResidenteAuthController::class, 'logout']);
        Route::get('/me', [App\Http\Controllers\Api\ResidenteAuthController::class, 'me']);
        
        // Rutas de reservas
        Route::get('/reservas', [App\Http\Controllers\Api\ReservaController::class, 'index']);
        Route::get('/reservas/buscar-invitados', [App\Http\Controllers\Api\ReservaController::class, 'buscarInvitados']);
        Route::post('/reservas', [App\Http\Controllers\Api\ReservaController::class, 'store']);
    });
});

// Incluir rutas modulares
// Las rutas de superadmin ahora están en web.php (rutas web con vistas)
// require __DIR__.'/api_superadmin.php';
require __DIR__.'/api_admin.php';
