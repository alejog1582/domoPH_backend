<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SuperAdmin API Routes (DEPRECATED)
|--------------------------------------------------------------------------
|
| Estas rutas están comentadas porque la administración se realiza
| desde las vistas web en lugar de API REST.
| 
| Si necesitas usar estas rutas API en el futuro, descomenta las rutas
| y actualiza los controladores para que devuelvan JSON.
|
*/

// Route::prefix('v1/superadmin')->middleware(['auth:sanctum', 'role:superadministrador'])->group(function () {
//     
//     // Gestión de Copropiedades
//     // Route::apiResource('propiedades', PropiedadController::class);
//     
//     // Gestión de Planes
//     // Route::apiResource('planes', PlanController::class);
//     
//     // Gestión de Módulos
//     // Route::get('modulos', [ModuloController::class, 'index']);
//     // Route::post('modulos', [ModuloController::class, 'store']);
//     // Route::put('modulos/{modulo}', [ModuloController::class, 'update']);
//     
//     // Gestión de Usuarios Administradores
//     // Route::apiResource('administradores', AdminController::class);
//     
//     // Configuraciones Globales
//     // Route::get('configuraciones', [ConfiguracionController::class, 'index']);
//     // Route::put('configuraciones', [ConfiguracionController::class, 'update']);
//     
//     // Auditoría
//     // Route::get('auditoria', [AuditoriaController::class, 'index']);
// });
