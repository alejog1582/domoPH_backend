<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\PropiedadController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\ModuloController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\ConfiguracionController;
use App\Http\Controllers\SuperAdmin\AuditoriaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // Si el usuario está autenticado y es superadministrador, redirigir al dashboard
    if (Auth::check() && Auth::user()->hasRole('superadministrador')) {
        return redirect()->route('superadmin.dashboard');
    }
    
    // Si no está autenticado, redirigir al login
    return redirect()->route('superadmin.login');
});

/*
|--------------------------------------------------------------------------
| SuperAdmin Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de administración del superadministrador
|
*/

// Rutas de autenticación (sin middleware de autenticación)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas protegidas del superadministrador
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'role:superadministrador'])->group(function () {
    
    // Dashboard
    Route::get('dashboard', function () {
        return view('superadmin.dashboard');
    })->name('dashboard');

    // Gestión de Copropiedades
    Route::resource('propiedades', PropiedadController::class);
    
    // Gestión de Planes
    Route::resource('planes', PlanController::class)->parameters([
        'planes' => 'plan'
    ]);
    
        // Gestión de Módulos
        Route::get('modulos', [ModuloController::class, 'index'])->name('modulos.index');
        Route::get('modulos/create', [ModuloController::class, 'create'])->name('modulos.create');
        Route::post('modulos', [ModuloController::class, 'store'])->name('modulos.store');
        Route::get('modulos/{modulo}/edit', [ModuloController::class, 'edit'])->name('modulos.edit');
        Route::put('modulos/{modulo}', [ModuloController::class, 'update'])->name('modulos.update');
        Route::delete('modulos/{modulo}', [ModuloController::class, 'destroy'])->name('modulos.destroy');
    
    // Gestión de Usuarios Administradores
    Route::resource('administradores', AdminController::class);
    
    // Configuraciones Globales
    Route::get('configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
    Route::put('configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');
    
    // Auditoría
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});
