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
use App\Http\Controllers\Admin\CarteraController;
use App\Http\Controllers\Admin\CuentaCobroController;
use App\Http\Controllers\Admin\RecaudoController;
use App\Http\Controllers\Admin\AcuerdoPagoController;
use App\Http\Controllers\Admin\ComunicadoController;
use App\Http\Controllers\Admin\CorrespondenciaController;
use App\Http\Controllers\Admin\VisitaController;
use App\Http\Controllers\Admin\AutorizacionController;
use App\Http\Controllers\Admin\LlamadoAtencionController;
use App\Http\Controllers\Admin\PqrsController;
use App\Http\Controllers\Admin\ReservaController;
use App\Http\Controllers\Admin\SorteoParqueaderoController;
use App\Http\Controllers\Admin\ParqueaderoController;
use App\Http\Controllers\Admin\ManualConvivenciaController;
use App\Http\Controllers\Admin\DepositoController;
use App\Http\Controllers\Admin\UsuarioAdminController;
use App\Http\Controllers\Admin\EncuestaVotacionController;
use App\Http\Controllers\Admin\LicitacionController;
use App\Http\Controllers\Publico\LicitacionPublicaController;
use App\Http\Controllers\Admin\ConsejoIntegranteController;
use App\Http\Controllers\Admin\ConsejoReunionController;
use App\Http\Controllers\Admin\ConsejoActaController;
use App\Http\Controllers\Admin\ConsejoDecisionController;
use App\Http\Controllers\Admin\ConsejoTareaController;
use App\Http\Controllers\Admin\ConsejoComunicacionController;
use App\Http\Controllers\Admin\AsambleaController;
use App\Http\Controllers\Admin\EcommerceCategoriaController;
use App\Http\Controllers\Admin\EcommerceController;
use App\Http\Controllers\Admin\ConfiguracionesPropiedadController;

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
    Route::resource('propiedades', PropiedadController::class)->parameters([
        'propiedades' => 'propiedad'
    ]);
    
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
        Route::get('administradores', [AdminController::class, 'index'])->name('administradores.index');
        Route::get('administradores/create', [AdminController::class, 'create'])->name('administradores.create');
        Route::post('administradores', [AdminController::class, 'store'])->name('administradores.store');
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
// Permite acceso a usuarios con rol administrador o usuarios con propiedad_id y roles asignados
Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\CheckAdminAccess::class])->group(function () {
    
    // Dashboard
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard')->middleware('permission:dashboard.view');
    
    // Gestión de Unidades
    Route::middleware('permission:unidades.view')->group(function () {
        Route::get('unidades', [UnidadController::class, 'index'])->name('unidades.index');
        Route::get('unidades/template', [UnidadController::class, 'downloadTemplate'])->name('unidades.template');
    });
    Route::middleware('permission:unidades.create')->group(function () {
        Route::get('unidades/create', [UnidadController::class, 'create'])->name('unidades.create');
        Route::post('unidades', [UnidadController::class, 'store'])->name('unidades.store');
        Route::post('unidades/import', [UnidadController::class, 'import'])->name('unidades.import');
    });
    Route::middleware('permission:unidades.edit')->group(function () {
        Route::get('unidades/{unidad}/edit', [UnidadController::class, 'edit'])->name('unidades.edit');
        Route::put('unidades/{unidad}', [UnidadController::class, 'update'])->name('unidades.update');
    });
    Route::middleware('permission:unidades.delete')->group(function () {
        Route::delete('unidades/{unidad}', [UnidadController::class, 'destroy'])->name('unidades.destroy');
    });
    
    // Gestión de Residentes
    Route::middleware('permission:residentes.view')->group(function () {
        Route::get('residentes', [ResidenteController::class, 'index'])->name('residentes.index');
        Route::get('residentes/template', [ResidenteController::class, 'downloadTemplate'])->name('residentes.template');
    });
    Route::middleware('permission:residentes.create')->group(function () {
        Route::get('residentes/create', [ResidenteController::class, 'create'])->name('residentes.create');
        Route::post('residentes', [ResidenteController::class, 'store'])->name('residentes.store');
        Route::post('residentes/import', [ResidenteController::class, 'import'])->name('residentes.import');
    });
    Route::middleware('permission:residentes.edit')->group(function () {
        Route::get('residentes/{residente}/edit', [ResidenteController::class, 'edit'])->name('residentes.edit');
        Route::put('residentes/{residente}', [ResidenteController::class, 'update'])->name('residentes.update');
    });
    Route::middleware('permission:residentes.delete')->group(function () {
        Route::delete('residentes/{residente}', [ResidenteController::class, 'destroy'])->name('residentes.destroy');
    });
    
    // Gestión de Mascotas
    Route::middleware('permission:mascotas.view')->group(function () {
        Route::get('mascotas', [MascotaController::class, 'index'])->name('mascotas.index');
        Route::get('mascotas/template', [MascotaController::class, 'downloadTemplate'])->name('mascotas.template');
    });
    Route::middleware('permission:mascotas.create')->group(function () {
        Route::get('mascotas/create', [MascotaController::class, 'create'])->name('mascotas.create');
        Route::post('mascotas', [MascotaController::class, 'store'])->name('mascotas.store');
        Route::post('mascotas/import', [MascotaController::class, 'import'])->name('mascotas.import');
    });
    Route::middleware('permission:mascotas.edit')->group(function () {
        Route::get('mascotas/{mascota}/edit', [MascotaController::class, 'edit'])->name('mascotas.edit');
        Route::put('mascotas/{mascota}', [MascotaController::class, 'update'])->name('mascotas.update');
    });
    Route::middleware('permission:mascotas.delete')->group(function () {
        Route::delete('mascotas/{mascota}', [MascotaController::class, 'destroy'])->name('mascotas.destroy');
    });
    
    // Gestión de Parqueaderos
    Route::middleware('permission:parqueaderos.view')->group(function () {
        Route::get('parqueaderos', [ParqueaderoController::class, 'index'])->name('parqueaderos.index');
        Route::get('parqueaderos/template', [ParqueaderoController::class, 'downloadTemplate'])->name('parqueaderos.template');
    });
    Route::middleware('permission:parqueaderos.create')->group(function () {
        Route::get('parqueaderos/create', [ParqueaderoController::class, 'create'])->name('parqueaderos.create');
        Route::post('parqueaderos', [ParqueaderoController::class, 'store'])->name('parqueaderos.store');
        Route::post('parqueaderos/import', [ParqueaderoController::class, 'import'])->name('parqueaderos.import');
    });
    Route::middleware('permission:parqueaderos.edit')->group(function () {
        Route::get('parqueaderos/{id}/edit', [ParqueaderoController::class, 'edit'])->name('parqueaderos.edit');
        Route::put('parqueaderos/{id}', [ParqueaderoController::class, 'update'])->name('parqueaderos.update');
    });
    Route::middleware('permission:parqueaderos.delete')->group(function () {
        Route::delete('parqueaderos/{id}', [ParqueaderoController::class, 'destroy'])->name('parqueaderos.destroy');
    });
    
    // Gestión de Depósitos
    Route::middleware('permission:depositos.view')->group(function () {
        Route::get('depositos', [DepositoController::class, 'index'])->name('depositos.index');
        Route::get('depositos/template', [DepositoController::class, 'downloadTemplate'])->name('depositos.template');
    });
    Route::middleware('permission:depositos.create')->group(function () {
        Route::get('depositos/create', [DepositoController::class, 'create'])->name('depositos.create');
        Route::post('depositos', [DepositoController::class, 'store'])->name('depositos.store');
        Route::post('depositos/import', [DepositoController::class, 'import'])->name('depositos.import');
    });
    Route::middleware('permission:depositos.edit')->group(function () {
        Route::get('depositos/{id}/edit', [DepositoController::class, 'edit'])->name('depositos.edit');
        Route::put('depositos/{id}', [DepositoController::class, 'update'])->name('depositos.update');
    });
    Route::middleware('permission:depositos.delete')->group(function () {
        Route::delete('depositos/{id}', [DepositoController::class, 'destroy'])->name('depositos.destroy');
    });
    
    // Gestión de Zonas Comunes
    Route::middleware('permission:zonas-sociales.view')->group(function () {
        Route::get('zonas-sociales', [ZonaSocialController::class, 'index'])->name('zonas-sociales.index');
    });
    Route::middleware('permission:zonas-sociales.create')->group(function () {
        Route::get('zonas-sociales/create', [ZonaSocialController::class, 'create'])->name('zonas-sociales.create');
        Route::post('zonas-sociales', [ZonaSocialController::class, 'store'])->name('zonas-sociales.store');
    });
    Route::middleware('permission:zonas-sociales.edit')->group(function () {
        Route::get('zonas-sociales/{zonaSocial}/edit', [ZonaSocialController::class, 'edit'])->name('zonas-sociales.edit');
        Route::put('zonas-sociales/{zonaSocial}', [ZonaSocialController::class, 'update'])->name('zonas-sociales.update');
    });
    Route::middleware('permission:zonas-sociales.delete')->group(function () {
        Route::delete('zonas-sociales/{zonaSocial}', [ZonaSocialController::class, 'destroy'])->name('zonas-sociales.destroy');
    });
    
    // Gestión de Cuotas de Administración
    Route::middleware('permission:cuotas-administracion.view')->group(function () {
        Route::get('cuotas-administracion', [CuotaAdministracionController::class, 'index'])->name('cuotas-administracion.index');
    });
    Route::middleware('permission:cuotas-administracion.create')->group(function () {
        Route::get('cuotas-administracion/create', [CuotaAdministracionController::class, 'create'])->name('cuotas-administracion.create');
        Route::post('cuotas-administracion', [CuotaAdministracionController::class, 'store'])->name('cuotas-administracion.store');
    });
    Route::middleware('permission:cuotas-administracion.edit')->group(function () {
        Route::get('cuotas-administracion/{cuotaAdministracion}/edit', [CuotaAdministracionController::class, 'edit'])->name('cuotas-administracion.edit');
        Route::put('cuotas-administracion/{cuotaAdministracion}', [CuotaAdministracionController::class, 'update'])->name('cuotas-administracion.update');
    });
    Route::middleware('permission:cuotas-administracion.delete')->group(function () {
        Route::delete('cuotas-administracion/{cuotaAdministracion}', [CuotaAdministracionController::class, 'destroy'])->name('cuotas-administracion.destroy');
    });
    
    // Gestión de Cartera de Unidades (Solo lectura)
    Route::middleware('permission:cartera.view')->group(function () {
        Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');
        Route::get('cartera/{cartera}/detalles', [CarteraController::class, 'detalles'])->name('cartera.detalles');
        Route::get('cartera/cargar-saldos', [CarteraController::class, 'showCargarSaldos'])->name('cartera.cargar-saldos');
        Route::get('cartera/template', [CarteraController::class, 'downloadTemplate'])->name('cartera.download-template');
        Route::post('cartera/import-saldos', [CarteraController::class, 'importSaldos'])->name('cartera.import-saldos');
    });
    
    // Gestión de Cuentas de Cobro
    Route::middleware('permission:cuentas-cobro.view')->group(function () {
        Route::get('cuentas-cobro', [CuentaCobroController::class, 'index'])->name('cuentas-cobro.index');
        Route::get('cuentas-cobro/recaudo/{recaudoId}', [CuentaCobroController::class, 'obtenerRecaudo'])->name('cuentas-cobro.recaudo');
    });
    
    // Gestión de Recaudos
    Route::middleware('permission:recaudos.view')->group(function () {
        Route::get('recaudos', [RecaudoController::class, 'index'])->name('recaudos.index');
        Route::get('recaudos/cargar', [RecaudoController::class, 'showCargarRecaudos'])->name('recaudos.cargar');
        Route::get('recaudos/template', [RecaudoController::class, 'downloadTemplate'])->name('recaudos.download-template');
        Route::post('recaudos/import', [RecaudoController::class, 'importRecaudos'])->name('recaudos.import');
    });
    
    // Gestión de Acuerdos de Pago
    Route::middleware('permission:acuerdos-pagos.view')->group(function () {
        Route::get('acuerdos-pagos', [AcuerdoPagoController::class, 'index'])->name('acuerdos-pagos.index');
    });
    Route::middleware('permission:acuerdos-pagos.create')->group(function () {
        Route::get('acuerdos-pagos/create', [AcuerdoPagoController::class, 'create'])->name('acuerdos-pagos.create');
        Route::post('acuerdos-pagos', [AcuerdoPagoController::class, 'store'])->name('acuerdos-pagos.store');
    });
    Route::middleware('permission:acuerdos-pagos.edit')->group(function () {
        Route::get('acuerdos-pagos/{acuerdoPago}/edit', [AcuerdoPagoController::class, 'edit'])->name('acuerdos-pagos.edit');
        Route::put('acuerdos-pagos/{acuerdoPago}', [AcuerdoPagoController::class, 'update'])->name('acuerdos-pagos.update');
    });
    
    // Gestión de Comunicados
    Route::middleware('permission:comunicados.view')->group(function () {
        Route::get('comunicados', [ComunicadoController::class, 'index'])->name('comunicados.index');
    });
    Route::middleware('permission:comunicados.create')->group(function () {
        Route::get('comunicados/create', [ComunicadoController::class, 'create'])->name('comunicados.create');
        Route::post('comunicados', [ComunicadoController::class, 'store'])->name('comunicados.store');
    });
    Route::middleware('permission:comunicados.edit')->group(function () {
        Route::get('comunicados/{comunicado}/edit', [ComunicadoController::class, 'edit'])->name('comunicados.edit');
        Route::put('comunicados/{comunicado}', [ComunicadoController::class, 'update'])->name('comunicados.update');
    });
    
    // Gestión de Correspondencias
    Route::middleware('permission:correspondencias.view')->group(function () {
        Route::get('correspondencias', [CorrespondenciaController::class, 'index'])->name('correspondencias.index');
        Route::get('correspondencias/cargar', [CorrespondenciaController::class, 'showCargarCorrespondencias'])->name('correspondencias.cargar');
        Route::get('correspondencias/template', [CorrespondenciaController::class, 'downloadTemplate'])->name('correspondencias.download-template');
    });
    
    // Gestión de Visitas
    Route::middleware('permission:visitas.view')->group(function () {
        Route::get('visitas', [VisitaController::class, 'index'])->name('visitas.index');
    });
    Route::middleware('permission:visitas.create')->group(function () {
        Route::get('visitas/create', [VisitaController::class, 'create'])->name('visitas.create');
        Route::post('visitas', [VisitaController::class, 'store'])->name('visitas.store');
        Route::post('visitas/{id}/activar', [VisitaController::class, 'activar'])->name('visitas.activar');
    });
    
    // Gestión de Autorizaciones
    Route::middleware('permission:autorizaciones.view')->group(function () {
        Route::get('autorizaciones', [AutorizacionController::class, 'index'])->name('autorizaciones.index');
    });
    Route::middleware('permission:autorizaciones.create')->group(function () {
        Route::get('autorizaciones/create', [AutorizacionController::class, 'create'])->name('autorizaciones.create');
        Route::post('autorizaciones', [AutorizacionController::class, 'store'])->name('autorizaciones.store');
    });
    
    // Gestión de Llamados de Atención
    Route::middleware('permission:llamados-atencion.view')->group(function () {
        Route::get('llamados-atencion', [LlamadoAtencionController::class, 'index'])->name('llamados-atencion.index');
    });
    Route::middleware('permission:llamados-atencion.create')->group(function () {
        Route::get('llamados-atencion/create', [LlamadoAtencionController::class, 'create'])->name('llamados-atencion.create');
        Route::post('llamados-atencion', [LlamadoAtencionController::class, 'store'])->name('llamados-atencion.store');
    });
    Route::middleware('permission:llamados-atencion.edit')->group(function () {
        Route::get('llamados-atencion/{id}/edit', [LlamadoAtencionController::class, 'edit'])->name('llamados-atencion.edit');
        Route::put('llamados-atencion/{id}', [LlamadoAtencionController::class, 'update'])->name('llamados-atencion.update');
    });
    
    // Gestión de PQRS
    Route::middleware('permission:pqrs.view')->group(function () {
        Route::get('pqrs', [PqrsController::class, 'index'])->name('pqrs.index');
    });
    Route::middleware('permission:pqrs.edit')->group(function () {
        Route::get('pqrs/{pqrs}/edit', [PqrsController::class, 'edit'])->name('pqrs.edit');
        Route::put('pqrs/{pqrs}', [PqrsController::class, 'update'])->name('pqrs.update');
    });
    
    // Gestión de Reservas
    Route::middleware('permission:reservas.view')->group(function () {
        Route::get('reservas', [ReservaController::class, 'index'])->name('reservas.index');
        Route::get('reservas/{reserva}', [ReservaController::class, 'show'])->name('reservas.show');
    });
    Route::middleware('permission:reservas.edit')->group(function () {
        Route::put('reservas/{reserva}', [ReservaController::class, 'update'])->name('reservas.update');
    });
    
    // Gestión de Sorteos Parqueaderos
    Route::middleware('permission:sorteos-parqueadero.view')->group(function () {
        Route::get('sorteos-parqueadero', [SorteoParqueaderoController::class, 'index'])->name('sorteos-parqueadero.index');
        Route::get('sorteos-parqueadero/{id}/participantes', [SorteoParqueaderoController::class, 'participantes'])->name('sorteos-parqueadero.participantes');
        Route::get('sorteos-parqueadero/{id}/datos-sorteo', [SorteoParqueaderoController::class, 'datosSorteo'])->name('sorteos-parqueadero.datos-sorteo');
        Route::get('sorteos-parqueadero/{id}/sorteo-manual', [SorteoParqueaderoController::class, 'sorteoManual'])->name('sorteos-parqueadero.sorteo-manual');
        Route::get('sorteos-parqueadero/{id}/sorteo-automatico', [SorteoParqueaderoController::class, 'sorteoAutomatico'])->name('sorteos-parqueadero.sorteo-automatico');
    });
    Route::middleware('permission:sorteos-parqueadero.create')->group(function () {
        Route::get('sorteos-parqueadero/create', [SorteoParqueaderoController::class, 'create'])->name('sorteos-parqueadero.create');
        Route::post('sorteos-parqueadero', [SorteoParqueaderoController::class, 'store'])->name('sorteos-parqueadero.store');
        Route::post('sorteos-parqueadero/{id}/iniciar-sorteo', [SorteoParqueaderoController::class, 'iniciarSorteo'])->name('sorteos-parqueadero.iniciar-sorteo');
        Route::post('sorteos-parqueadero/{id}/asignar-parqueadero', [SorteoParqueaderoController::class, 'asignarParqueadero'])->name('sorteos-parqueadero.asignar-parqueadero');
        Route::post('sorteos-parqueadero/{id}/asignar-balota-blanca', [SorteoParqueaderoController::class, 'asignarBalotaBlanca'])->name('sorteos-parqueadero.asignar-balota-blanca');
        Route::post('sorteos-parqueadero/{id}/ejecutar-sorteo-automatico', [SorteoParqueaderoController::class, 'ejecutarSorteoAutomatico'])->name('sorteos-parqueadero.ejecutar-sorteo-automatico');
    });
    Route::middleware('permission:sorteos-parqueadero.edit')->group(function () {
        Route::get('sorteos-parqueadero/{id}/edit', [SorteoParqueaderoController::class, 'edit'])->name('sorteos-parqueadero.edit');
        Route::put('sorteos-parqueadero/{id}', [SorteoParqueaderoController::class, 'update'])->name('sorteos-parqueadero.update');
    });
    
    // Gestión de Manual de Convivencia
    Route::middleware('permission:manual-convivencia.view')->group(function () {
        Route::get('manual-convivencia', [ManualConvivenciaController::class, 'index'])->name('manual-convivencia.index');
    });
    Route::middleware('permission:manual-convivencia.edit')->group(function () {
        Route::post('manual-convivencia', [ManualConvivenciaController::class, 'store'])->name('manual-convivencia.store');
    });
    
    // Gestión de Usuarios Admin
    Route::middleware('permission:usuarios-admin.view')->group(function () {
        Route::get('usuarios-admin', [UsuarioAdminController::class, 'index'])->name('usuarios-admin.index');
    });
    Route::middleware('permission:usuarios-admin.create')->group(function () {
        Route::get('usuarios-admin/create', [UsuarioAdminController::class, 'create'])->name('usuarios-admin.create');
        Route::post('usuarios-admin', [UsuarioAdminController::class, 'store'])->name('usuarios-admin.store');
    });
    Route::middleware('permission:usuarios-admin.edit')->group(function () {
        Route::get('usuarios-admin/{usuarioAdmin}/edit', [UsuarioAdminController::class, 'edit'])->name('usuarios-admin.edit');
        Route::put('usuarios-admin/{usuarioAdmin}', [UsuarioAdminController::class, 'update'])->name('usuarios-admin.update');
    });
    Route::middleware('permission:usuarios-admin.delete')->group(function () {
        Route::delete('usuarios-admin/{usuarioAdmin}', [UsuarioAdminController::class, 'destroy'])->name('usuarios-admin.destroy');
    });
    
    // Gestión de Encuestas y Votaciones
    Route::middleware('permission:encuestas.view,votaciones.view')->group(function () {
        Route::get('encuestas-votaciones', [EncuestaVotacionController::class, 'index'])->name('encuestas-votaciones.index');
    });
    Route::middleware('permission:encuestas.create,votaciones.create')->group(function () {
        Route::get('encuestas-votaciones/create', [EncuestaVotacionController::class, 'create'])->name('encuestas-votaciones.create');
        Route::post('encuestas-votaciones', [EncuestaVotacionController::class, 'store'])->name('encuestas-votaciones.store');
    });
    Route::middleware('permission:encuestas.edit,votaciones.edit')->group(function () {
        Route::get('encuestas-votaciones/{id}/edit', [EncuestaVotacionController::class, 'edit'])->name('encuestas-votaciones.edit');
        Route::put('encuestas-votaciones/{id}', [EncuestaVotacionController::class, 'update'])->name('encuestas-votaciones.update');
    });
    Route::middleware('permission:encuestas.respuestas,votaciones.resultados')->group(function () {
        Route::get('encuestas-votaciones/{id}', [EncuestaVotacionController::class, 'show'])->name('encuestas-votaciones.show');
        Route::get('encuestas/{id}/respuestas', [EncuestaVotacionController::class, 'show'])->name('encuestas.respuestas');
        Route::get('votaciones/{id}/resultados', [EncuestaVotacionController::class, 'show'])->name('votaciones.resultados');
    });
    Route::middleware('permission:encuestas.delete,votaciones.delete')->group(function () {
        Route::delete('encuestas-votaciones/{id}', [EncuestaVotacionController::class, 'destroy'])->name('encuestas-votaciones.destroy');
    });

    // Gestión de Cartelera de Licitaciones
    Route::middleware('permission:licitaciones.create')->group(function () {
        Route::get('licitaciones/create', [LicitacionController::class, 'create'])->name('licitaciones.create');
        Route::post('licitaciones', [LicitacionController::class, 'store'])->name('licitaciones.store');
    });
    Route::middleware('permission:licitaciones.edit')->group(function () {
        Route::get('licitaciones/{id}/edit', [LicitacionController::class, 'edit'])->name('licitaciones.edit');
        Route::put('licitaciones/{id}', [LicitacionController::class, 'update'])->name('licitaciones.update');
        Route::post('licitaciones/{id}/adjudicar', [LicitacionController::class, 'adjudicar'])->name('licitaciones.adjudicar');
    });
    Route::middleware('permission:licitaciones.view')->group(function () {
        Route::get('licitaciones', [LicitacionController::class, 'index'])->name('licitaciones.index');
        Route::get('licitaciones/{id}', [LicitacionController::class, 'show'])->name('licitaciones.show');
        Route::get('ofertas/{id}', [LicitacionController::class, 'getOferta'])->name('ofertas.show');
    });
    Route::middleware('permission:licitaciones.delete')->group(function () {
        Route::delete('licitaciones/{id}', [LicitacionController::class, 'destroy'])->name('licitaciones.destroy');
    });

    // ========================
    // CONSEJO DE ADMINISTRACIÓN
    // ========================
    
    // Consejo - Integrantes
    Route::middleware('permission:consejo-integrantes.view')->group(function () {
        Route::get('consejo-integrantes', [ConsejoIntegranteController::class, 'index'])->name('consejo-integrantes.index');
    });
    Route::middleware('permission:consejo-integrantes.create')->group(function () {
        Route::get('consejo-integrantes/create', [ConsejoIntegranteController::class, 'create'])->name('consejo-integrantes.create');
        Route::post('consejo-integrantes', [ConsejoIntegranteController::class, 'store'])->name('consejo-integrantes.store');
    });
    Route::middleware('permission:consejo-integrantes.edit')->group(function () {
        Route::get('consejo-integrantes/{id}/edit', [ConsejoIntegranteController::class, 'edit'])->name('consejo-integrantes.edit');
        Route::put('consejo-integrantes/{id}', [ConsejoIntegranteController::class, 'update'])->name('consejo-integrantes.update');
    });
    Route::middleware('permission:consejo-integrantes.view')->group(function () {
        Route::get('consejo-integrantes/{id}', [ConsejoIntegranteController::class, 'show'])->name('consejo-integrantes.show');
    });
    
    // Consejo - Reuniones
    Route::middleware('permission:consejo-reuniones.view')->group(function () {
        Route::get('consejo-reuniones', [ConsejoReunionController::class, 'index'])->name('consejo-reuniones.index');
    });
    Route::middleware('permission:consejo-reuniones.create')->group(function () {
        Route::get('consejo-reuniones/create', [ConsejoReunionController::class, 'create'])->name('consejo-reuniones.create');
        Route::post('consejo-reuniones', [ConsejoReunionController::class, 'store'])->name('consejo-reuniones.store');
    });
    Route::middleware('permission:consejo-reuniones.edit')->group(function () {
        Route::get('consejo-reuniones/{id}/edit', [ConsejoReunionController::class, 'edit'])->name('consejo-reuniones.edit');
        Route::put('consejo-reuniones/{id}', [ConsejoReunionController::class, 'update'])->name('consejo-reuniones.update');
    });
    Route::middleware('permission:consejo-reuniones.view')->group(function () {
        Route::get('consejo-reuniones/{id}', [ConsejoReunionController::class, 'show'])->name('consejo-reuniones.show');
    });
    
    // Actas de Reuniones
    Route::middleware('permission:consejo-actas.view')->group(function () {
        Route::get('consejo-actas', [ConsejoActaController::class, 'index'])->name('consejo-actas.index');
    });
    Route::middleware('permission:consejo-actas.create')->group(function () {
        Route::get('consejo-actas/create', [ConsejoActaController::class, 'create'])->name('consejo-actas.create');
        Route::post('consejo-actas', [ConsejoActaController::class, 'store'])->name('consejo-actas.store');
    });
    Route::middleware('permission:consejo-actas.edit')->group(function () {
        Route::get('consejo-actas/{id}/edit', [ConsejoActaController::class, 'edit'])->name('consejo-actas.edit');
        Route::put('consejo-actas/{id}', [ConsejoActaController::class, 'update'])->name('consejo-actas.update');
    });
    Route::middleware('permission:consejo-actas.delete')->group(function () {
        Route::delete('consejo-actas/{id}', [ConsejoActaController::class, 'destroy'])->name('consejo-actas.destroy');
    });
    Route::middleware('permission:consejo-actas.firmar')->group(function () {
        Route::post('consejo-actas/{id}/firmar', [ConsejoActaController::class, 'firmar'])->name('consejo-actas.firmar');
    });
    Route::middleware('permission:consejo-actas.edit')->group(function () {
        Route::post('consejo-actas/{id}/eliminar-firmas', [ConsejoActaController::class, 'eliminarFirmas'])->name('consejo-actas.eliminar-firmas');
    });
    Route::middleware('permission:consejo-actas.view')->group(function () {
        Route::get('consejo-actas/{id}', [ConsejoActaController::class, 'show'])->name('consejo-actas.show');
    });
    
    // Decisiones del Consejo
    Route::middleware('permission:consejo-decisiones.view')->group(function () {
        Route::get('consejo-decisiones', [ConsejoDecisionController::class, 'index'])->name('consejo-decisiones.index');
    });
    Route::middleware('permission:consejo-decisiones.create')->group(function () {
        Route::get('consejo-decisiones/create', [ConsejoDecisionController::class, 'create'])->name('consejo-decisiones.create');
        Route::post('consejo-decisiones', [ConsejoDecisionController::class, 'store'])->name('consejo-decisiones.store');
    });
    Route::middleware('permission:consejo-decisiones.edit')->group(function () {
        Route::get('consejo-decisiones/{id}/edit', [ConsejoDecisionController::class, 'edit'])->name('consejo-decisiones.edit');
        Route::put('consejo-decisiones/{id}', [ConsejoDecisionController::class, 'update'])->name('consejo-decisiones.update');
    });
    Route::middleware('permission:consejo-decisiones.delete')->group(function () {
        Route::delete('consejo-decisiones/{id}', [ConsejoDecisionController::class, 'destroy'])->name('consejo-decisiones.destroy');
    });
    Route::middleware('permission:consejo-decisiones.view')->group(function () {
        Route::get('consejo-decisiones/{id}', [ConsejoDecisionController::class, 'show'])->name('consejo-decisiones.show');
    });
    
    // Tareas y Seguimiento
    Route::middleware('permission:consejo-tareas.view')->group(function () {
        Route::get('consejo-tareas', [ConsejoTareaController::class, 'index'])->name('consejo-tareas.index');
    });
    Route::middleware('permission:consejo-tareas.create')->group(function () {
        Route::get('consejo-tareas/create', [ConsejoTareaController::class, 'create'])->name('consejo-tareas.create');
        Route::post('consejo-tareas', [ConsejoTareaController::class, 'store'])->name('consejo-tareas.store');
        Route::get('consejo-tareas/get-decisiones', [ConsejoTareaController::class, 'getDecisiones'])->name('consejo-tareas.get-decisiones');
    });
    Route::middleware('permission:consejo-tareas.edit')->group(function () {
        Route::get('consejo-tareas/{id}/edit', [ConsejoTareaController::class, 'edit'])->name('consejo-tareas.edit');
        Route::put('consejo-tareas/{id}', [ConsejoTareaController::class, 'update'])->name('consejo-tareas.update');
    });
    Route::middleware('permission:consejo-tareas.delete')->group(function () {
        Route::delete('consejo-tareas/{id}', [ConsejoTareaController::class, 'destroy'])->name('consejo-tareas.destroy');
    });
    Route::middleware('permission:consejo-tareas.seguimiento')->group(function () {
        Route::post('consejo-tareas/{id}/seguimiento', [ConsejoTareaController::class, 'agregarSeguimiento'])->name('consejo-tareas.seguimiento');
        Route::post('consejo-tareas/{id}/archivos', [ConsejoTareaController::class, 'subirArchivo'])->name('consejo-tareas.archivos');
    });
    Route::middleware('permission:consejo-tareas.view')->group(function () {
        Route::get('consejo-tareas/{id}/gestionar', [ConsejoTareaController::class, 'gestionar'])->name('consejo-tareas.gestionar');
        Route::get('consejo-tareas/{id}', [ConsejoTareaController::class, 'show'])->name('consejo-tareas.show');
    });
    
    // Comunicaciones del Consejo
    Route::middleware('permission:consejo-comunicaciones.view')->group(function () {
        Route::get('consejo-comunicaciones', [ConsejoComunicacionController::class, 'index'])->name('consejo-comunicaciones.index');
    });
    Route::middleware('permission:consejo-comunicaciones.create')->group(function () {
        Route::get('consejo-comunicaciones/create', [ConsejoComunicacionController::class, 'create'])->name('consejo-comunicaciones.create');
        Route::post('consejo-comunicaciones', [ConsejoComunicacionController::class, 'store'])->name('consejo-comunicaciones.store');
    });
    Route::middleware('permission:consejo-comunicaciones.edit')->group(function () {
        Route::get('consejo-comunicaciones/{id}/edit', [ConsejoComunicacionController::class, 'edit'])->name('consejo-comunicaciones.edit');
        Route::put('consejo-comunicaciones/{id}', [ConsejoComunicacionController::class, 'update'])->name('consejo-comunicaciones.update');
    });
    Route::middleware('permission:consejo-comunicaciones.delete')->group(function () {
        Route::delete('consejo-comunicaciones/{id}', [ConsejoComunicacionController::class, 'destroy'])->name('consejo-comunicaciones.destroy');
    });
    Route::middleware('permission:consejo-comunicaciones.publicar')->group(function () {
        Route::post('consejo-comunicaciones/{id}/publicar', [ConsejoComunicacionController::class, 'publicar'])->name('consejo-comunicaciones.publicar');
    });
    Route::middleware('permission:consejo-comunicaciones.view')->group(function () {
        Route::get('consejo-comunicaciones/{id}', [ConsejoComunicacionController::class, 'show'])->name('consejo-comunicaciones.show');
    });
    
    // Asambleas
    Route::middleware('permission:asambleas.view')->group(function () {
        Route::get('asambleas', [AsambleaController::class, 'index'])->name('asambleas.index');
    });
    Route::middleware('permission:asambleas.create')->group(function () {
        Route::get('asambleas/create', [AsambleaController::class, 'create'])->name('asambleas.create');
        Route::post('asambleas', [AsambleaController::class, 'store'])->name('asambleas.store');
    });
    Route::middleware('permission:asambleas.edit')->group(function () {
        Route::get('asambleas/{id}/edit', [AsambleaController::class, 'edit'])->name('asambleas.edit');
        Route::put('asambleas/{id}', [AsambleaController::class, 'update'])->name('asambleas.update');
    });
    Route::middleware('permission:asambleas.delete')->group(function () {
        Route::delete('asambleas/{id}', [AsambleaController::class, 'destroy'])->name('asambleas.destroy');
    });
    Route::middleware('permission:asambleas.votaciones')->group(function () {
        Route::post('asambleas/{id}/votaciones', [AsambleaController::class, 'storeVotacion'])->name('asambleas.store-votacion');
        Route::post('asambleas/{asamblea}/votaciones/{votacion}/cerrar', [AsambleaController::class, 'cerrarVotacion'])->name('asambleas.cerrar-votacion');
    });
    Route::middleware('permission:asambleas.view')->group(function () {
        Route::get('asambleas/{id}', [AsambleaController::class, 'show'])->name('asambleas.show');
    });
    
    // ========================
    // ECOMMERCE / CLASIFICADOS
    // ========================
    
    // Gestión de Categorías Ecommerce
    Route::middleware('permission:ecommerce-categorias.view')->group(function () {
        Route::get('ecommerce-categorias', [EcommerceCategoriaController::class, 'index'])->name('ecommerce-categorias.index');
    });
    Route::middleware('permission:ecommerce-categorias.create')->group(function () {
        Route::get('ecommerce-categorias/create', [EcommerceCategoriaController::class, 'create'])->name('ecommerce-categorias.create');
        Route::post('ecommerce-categorias', [EcommerceCategoriaController::class, 'store'])->name('ecommerce-categorias.store');
    });
    Route::middleware('permission:ecommerce-categorias.edit')->group(function () {
        Route::get('ecommerce-categorias/{id}/edit', [EcommerceCategoriaController::class, 'edit'])->name('ecommerce-categorias.edit');
        Route::put('ecommerce-categorias/{id}', [EcommerceCategoriaController::class, 'update'])->name('ecommerce-categorias.update');
    });
    Route::middleware('permission:ecommerce-categorias.delete')->group(function () {
        Route::delete('ecommerce-categorias/{id}', [EcommerceCategoriaController::class, 'destroy'])->name('ecommerce-categorias.destroy');
    });
    Route::middleware('permission:ecommerce-categorias.view')->group(function () {
        Route::get('ecommerce-categorias/{id}', [EcommerceCategoriaController::class, 'show'])->name('ecommerce-categorias.show');
    });
    
    // Gestión de Publicaciones Ecommerce (Productos)
    Route::middleware('permission:ecommerce.view')->group(function () {
        Route::get('ecommerce', [EcommerceController::class, 'index'])->name('ecommerce.index');
    });
    Route::middleware('permission:ecommerce.create')->group(function () {
        Route::get('ecommerce/create', [EcommerceController::class, 'create'])->name('ecommerce.create');
        Route::post('ecommerce', [EcommerceController::class, 'store'])->name('ecommerce.store');
    });
    Route::middleware('permission:ecommerce.edit')->group(function () {
        Route::get('ecommerce/{id}/edit', [EcommerceController::class, 'edit'])->name('ecommerce.edit');
        Route::put('ecommerce/{id}', [EcommerceController::class, 'update'])->name('ecommerce.update');
    });
    Route::middleware('permission:ecommerce.delete')->group(function () {
        Route::delete('ecommerce/{id}', [EcommerceController::class, 'destroy'])->name('ecommerce.destroy');
    });
    Route::middleware('permission:ecommerce.view')->group(function () {
        Route::get('ecommerce/{id}', [EcommerceController::class, 'show'])->name('ecommerce.show');
    });
    Route::middleware('permission:ecommerce.aprobar')->group(function () {
        Route::post('ecommerce/{id}/aprobar', [EcommerceController::class, 'aprobar'])->name('ecommerce.aprobar');
    });
    Route::middleware('permission:ecommerce.pausar')->group(function () {
        Route::post('ecommerce/{id}/pausar', [EcommerceController::class, 'pausar'])->name('ecommerce.pausar');
    });
    Route::middleware('permission:ecommerce.finalizar')->group(function () {
        Route::post('ecommerce/{id}/finalizar', [EcommerceController::class, 'finalizar'])->name('ecommerce.finalizar');
    });
    
    // ========================
    // CONFIGURACIONES PROPIEDAD
    // ========================
    Route::middleware('permission:configuraciones-propiedad.view')->group(function () {
        Route::get('configuraciones-propiedad', [ConfiguracionesPropiedadController::class, 'index'])->name('configuraciones-propiedad.index');
    });
    Route::middleware('permission:configuraciones-propiedad.edit')->group(function () {
        Route::put('configuraciones-propiedad/{id}', [ConfiguracionesPropiedadController::class, 'update'])->name('configuraciones-propiedad.update');
        Route::post('configuraciones-propiedad/update-multiple', [ConfiguracionesPropiedadController::class, 'updateMultiple'])->name('configuraciones-propiedad.update-multiple');
    });
});

// Rutas públicas para proveedores (sin autenticación)
Route::prefix('licitaciones-publicas')->name('licitaciones-publicas.')->group(function () {
    Route::get('propiedad/{propiedad_id}', [LicitacionPublicaController::class, 'index'])->name('index');
    Route::get('licitacion/{id}', [LicitacionPublicaController::class, 'show'])->name('show');
    Route::get('licitacion/{id}/ofertar', [LicitacionPublicaController::class, 'createOferta'])->name('create-oferta');
    Route::post('licitacion/{id}/ofertar', [LicitacionPublicaController::class, 'storeOferta'])->name('store-oferta');
});
