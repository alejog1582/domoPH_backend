<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\SuperAdmin\PropiedadController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\ModuloController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\ConfiguracionController;
use App\Http\Controllers\SuperAdmin\AuditoriaController;
use App\Http\Controllers\Admin\UnidadController;
use App\Http\Controllers\Admin\ResidenteController;
use App\Http\Controllers\Admin\MascotaController;
use App\Http\Controllers\Admin\ZonaSocialController;
use App\Http\Controllers\Admin\CuotaAdministracionController;

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
    if (Auth::check()) {
        $user = Auth::user();
        
        // Si es superadministrador, redirigir al dashboard de superadmin
        if ($user->hasRole('superadministrador')) {
            return redirect()->route('superadmin.dashboard');
        }
        
        // Si es administrador, redirigir al dashboard de admin
        if ($user->hasRole('administrador')) {
            return redirect()->route('admin.dashboard');
        }
    }
    
    // Si no está autenticado, redirigir al login de admin por defecto
    return redirect()->route('admin.login');
});

// Ruta genérica de login para el middleware de autenticación
// Redirige a admin.login por defecto
Route::get('login', function () {
    return redirect()->route('admin.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| SuperAdmin Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de administración del superadministrador
|
*/

// Rutas de autenticación SuperAdmin (sin middleware de autenticación)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
});

// Rutas de autenticación Admin (sin middleware de autenticación)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
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
    
        // Gestión de Usuarios Administradores (solo listar y editar, no crear ni eliminar)
        Route::get('administradores', [AdminController::class, 'index'])->name('administradores.index');
        Route::get('administradores/{administrador}/edit', [AdminController::class, 'edit'])->name('administradores.edit');
        Route::put('administradores/{administrador}', [AdminController::class, 'update'])->name('administradores.update');
    
    // Configuraciones Globales
    Route::get('configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
    Route::put('configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');
    
    // Auditoría
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de administración de propiedades
|
*/

// Rutas protegidas del administrador
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:administrador'])->group(function () {
    
    // Dashboard
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    // Gestión de Unidades
    Route::get('unidades', [UnidadController::class, 'index'])->name('unidades.index');
    Route::get('unidades/create', [UnidadController::class, 'create'])->name('unidades.create');
    Route::post('unidades', [UnidadController::class, 'store'])->name('unidades.store');
    Route::get('unidades/template', [UnidadController::class, 'downloadTemplate'])->name('unidades.template');
    Route::post('unidades/import', [UnidadController::class, 'import'])->name('unidades.import');
    Route::get('unidades/{unidad}/edit', [UnidadController::class, 'edit'])->name('unidades.edit');
    Route::put('unidades/{unidad}', [UnidadController::class, 'update'])->name('unidades.update');
    Route::delete('unidades/{unidad}', [UnidadController::class, 'destroy'])->name('unidades.destroy');
    
    // Gestión de Residentes
    Route::get('residentes', [ResidenteController::class, 'index'])->name('residentes.index');
    Route::get('residentes/create', [ResidenteController::class, 'create'])->name('residentes.create');
    Route::post('residentes', [ResidenteController::class, 'store'])->name('residentes.store');
    Route::get('residentes/template', [ResidenteController::class, 'downloadTemplate'])->name('residentes.template');
    Route::post('residentes/import', [ResidenteController::class, 'import'])->name('residentes.import');
    Route::get('residentes/{residente}/edit', [ResidenteController::class, 'edit'])->name('residentes.edit');
    Route::put('residentes/{residente}', [ResidenteController::class, 'update'])->name('residentes.update');
    Route::delete('residentes/{residente}', [ResidenteController::class, 'destroy'])->name('residentes.destroy');
    
    // Gestión de Mascotas
    Route::get('mascotas', [MascotaController::class, 'index'])->name('mascotas.index');
    Route::get('mascotas/create', [MascotaController::class, 'create'])->name('mascotas.create');
    Route::post('mascotas', [MascotaController::class, 'store'])->name('mascotas.store');
    Route::get('mascotas/template', [MascotaController::class, 'downloadTemplate'])->name('mascotas.template');
    Route::post('mascotas/import', [MascotaController::class, 'import'])->name('mascotas.import');
    Route::get('mascotas/{mascota}/edit', [MascotaController::class, 'edit'])->name('mascotas.edit');
    Route::put('mascotas/{mascota}', [MascotaController::class, 'update'])->name('mascotas.update');
    Route::delete('mascotas/{mascota}', [MascotaController::class, 'destroy'])->name('mascotas.destroy');
    
    // Gestión de Zonas Comunes
    Route::get('zonas-sociales', [ZonaSocialController::class, 'index'])->name('zonas-sociales.index');
    Route::get('zonas-sociales/create', [ZonaSocialController::class, 'create'])->name('zonas-sociales.create');
    Route::post('zonas-sociales', [ZonaSocialController::class, 'store'])->name('zonas-sociales.store');
    Route::get('zonas-sociales/{zonaSocial}/edit', [ZonaSocialController::class, 'edit'])->name('zonas-sociales.edit');
    Route::put('zonas-sociales/{zonaSocial}', [ZonaSocialController::class, 'update'])->name('zonas-sociales.update');
    Route::delete('zonas-sociales/{zonaSocial}', [ZonaSocialController::class, 'destroy'])->name('zonas-sociales.destroy');
    
    // Gestión de Cuotas de Administración
    Route::get('cuotas-administracion', [CuotaAdministracionController::class, 'index'])->name('cuotas-administracion.index');
    Route::get('cuotas-administracion/create', [CuotaAdministracionController::class, 'create'])->name('cuotas-administracion.create');
    Route::post('cuotas-administracion', [CuotaAdministracionController::class, 'store'])->name('cuotas-administracion.store');
    Route::get('cuotas-administracion/{cuotaAdministracion}/edit', [CuotaAdministracionController::class, 'edit'])->name('cuotas-administracion.edit');
    Route::put('cuotas-administracion/{cuotaAdministracion}', [CuotaAdministracionController::class, 'update'])->name('cuotas-administracion.update');
    Route::delete('cuotas-administracion/{cuotaAdministracion}', [CuotaAdministracionController::class, 'destroy'])->name('cuotas-administracion.destroy');
    
    // Aquí se pueden agregar más rutas para los módulos del administrador
});
