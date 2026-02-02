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
        Route::post('/reservas/{id}/soporte-pago', [App\Http\Controllers\Api\ReservaController::class, 'actualizarSoportePago']);
        
        // Rutas de comunicados
        Route::get('/comunicados', [App\Http\Controllers\Api\ComunicadoController::class, 'index']);
        Route::get('/comunicados/{id}', [App\Http\Controllers\Api\ComunicadoController::class, 'show']);
        Route::post('/comunicados/{id}/marcar-leido', [App\Http\Controllers\Api\ComunicadoController::class, 'marcarLeido']);
        
        // Rutas de visitas
        Route::get('/visitas', [App\Http\Controllers\Api\VisitaController::class, 'index']);
        Route::post('/visitas', [App\Http\Controllers\Api\VisitaController::class, 'store']);
        
        // Rutas de llamados de atención
        Route::get('/llamados-atencion', [App\Http\Controllers\Api\LlamadoAtencionController::class, 'index']);
        Route::get('/llamados-atencion/{id}/historial', [App\Http\Controllers\Api\LlamadoAtencionController::class, 'historial']);
        Route::post('/llamados-atencion/{id}/respuesta', [App\Http\Controllers\Api\LlamadoAtencionController::class, 'agregarRespuesta']);
        
        // Rutas de PQRS
        Route::get('/pqrs', [App\Http\Controllers\Api\PqrsController::class, 'index']);
        Route::get('/pqrs/{id}', [App\Http\Controllers\Api\PqrsController::class, 'show']);
        Route::post('/pqrs', [App\Http\Controllers\Api\PqrsController::class, 'store']);
        Route::post('/pqrs/{id}/respuesta', [App\Http\Controllers\Api\PqrsController::class, 'agregarRespuesta']);

        // Rutas de Cartera
        Route::get('/cartera', [App\Http\Controllers\Api\CarteraController::class, 'index']);
        Route::post('/cartera/solicitar-acuerdo-pago', [App\Http\Controllers\Api\CarteraController::class, 'solicitarAcuerdoPago']);

        // Rutas de Autorizaciones
        Route::get('/autorizaciones', [App\Http\Controllers\Api\AutorizacionController::class, 'index']);
        Route::post('/autorizaciones', [App\Http\Controllers\Api\AutorizacionController::class, 'store']);
        Route::put('/autorizaciones/{id}', [App\Http\Controllers\Api\AutorizacionController::class, 'update']);
    });
});

// Incluir rutas modulares
// Las rutas de superadmin ahora están en web.php (rutas web con vistas)
// require __DIR__.'/api_superadmin.php';
require __DIR__.'/api_admin.php';
