<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\PropiedadController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\ModuloController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\ConfiguracionController;
use App\Http\Controllers\SuperAdmin\AuditoriaController;

/*
|--------------------------------------------------------------------------
| SuperAdmin API Routes
|--------------------------------------------------------------------------
|
| Rutas exclusivas para el superadministrador del SaaS
| Requiere autenticación y rol de superadministrador
|
*/

Route::prefix('v1/superadmin')->middleware(['auth:sanctum', 'role:superadministrador'])->group(function () {
    
    // Gestión de Copropiedades
    Route::apiResource('propiedades', PropiedadController::class);
    
    // Gestión de Planes
    Route::apiResource('planes', PlanController::class);
    
    // Gestión de Módulos
    Route::get('modulos', [ModuloController::class, 'index']);
    Route::post('modulos', [ModuloController::class, 'store']);
    Route::put('modulos/{modulo}', [ModuloController::class, 'update']);
    
    // Gestión de Usuarios Administradores
    Route::apiResource('administradores', AdminController::class);
    
    // Configuraciones Globales
    Route::get('configuraciones', [ConfiguracionController::class, 'index']);
    Route::put('configuraciones', [ConfiguracionController::class, 'update']);
    
    // Auditoría
    Route::get('auditoria', [AuditoriaController::class, 'index']);
});
