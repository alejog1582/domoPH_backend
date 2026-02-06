@php
    $modulos = \App\Helpers\AdminHelper::getModulosActivos();
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
    
    // Verificar permisos para cada sección del menú
    $hasCopropiedadPerms = \App\Helpers\AdminHelper::hasAnyPermission([
        'unidades.view', 'residentes.view', 'mascotas.view', 
        'parqueaderos.view', 'depositos.view', 'zonas-sociales.view'
    ]);
    
    $hasCarteraPerms = \App\Helpers\AdminHelper::hasAnyPermission([
        'cuotas-administracion.view', 'cartera.view', 'cuentas-cobro.view',
        'recaudos.view', 'acuerdos-pagos.view'
    ]);
    
    $hasGestionPerms = \App\Helpers\AdminHelper::hasAnyPermission([
        'comunicados.view', 'correspondencias.view', 'visitas.view', 'autorizaciones.view'
    ]);
    
    $hasConvivenciaPerms = \App\Helpers\AdminHelper::hasAnyPermission([
        'llamados-atencion.view', 'pqrs.view'
    ]);
@endphp

<!-- Logo -->
<div class="mb-6 px-2">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center">
        @if($propiedad && $propiedad->logo)
            <img src="{{ $propiedad->logo }}" alt="{{ $propiedad->nombre }}" class="h-12 w-auto object-contain mr-2">
        @else
            <img src="{{ asset('imagenes/logo.png') }}" alt="domoPH Logo" class="h-12 w-auto object-contain mr-2">
        @endif
        <div class="flex flex-col">
            <span class="text-xl font-bold text-gray-800">domoPH</span>
            <span class="text-xs text-gray-500">Admin</span>
        </div>
    </a>
</div>

<!-- Dashboard -->
@if(\App\Helpers\AdminHelper::hasPermission('dashboard.view'))
    <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        <i class="fas fa-home mr-3"></i>
        Dashboard
    </a>
@endif

@if($propiedad)
    <!-- Menú Copropiedad con submenú -->
    @if($hasCopropiedadPerms)
    <div class="mb-1">
        <button 
            type="button" 
            onclick="toggleSubmenu('copropiedad-menu')"
            class="group w-full flex items-center justify-between px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.unidades.*') || request()->routeIs('admin.copropiedad.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <div class="flex items-center">
                <i class="fas fa-building mr-3"></i>
                <span>Copropiedad</span>
            </div>
            <i id="copropiedad-menu-icon" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
        </button>
        
        <!-- Submenú de Copropiedad -->
        <div id="copropiedad-menu" class="hidden pl-4 mt-1 space-y-1">
            @if(\App\Helpers\AdminHelper::hasPermission('unidades.view'))
            <a 
                href="{{ route('admin.unidades.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.unidades.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-door-open mr-3 text-xs"></i>
                Unidades
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('residentes.view'))
            <a 
                href="{{ route('admin.residentes.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.residentes.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-users mr-3 text-xs"></i>
                Residentes
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('mascotas.view'))
            <a 
                href="{{ route('admin.mascotas.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.mascotas.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-paw mr-3 text-xs"></i>
                Mascotas
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('parqueaderos.view'))
            <a 
                href="{{ route('admin.parqueaderos.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.parqueaderos.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-car mr-3 text-xs"></i>
                Parqueaderos
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('depositos.view'))
            <a 
                href="{{ route('admin.depositos.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.depositos.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-warehouse mr-3 text-xs"></i>
                Depósitos
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('zonas-sociales.view'))
            <a 
                href="{{ route('admin.zonas-sociales.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.zonas-sociales.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-swimming-pool mr-3 text-xs"></i>
                Zonas Comunes
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Menú Cartera con submenú -->
    @if($hasCarteraPerms)
    <div class="mb-1">
        <button 
            type="button" 
            onclick="toggleSubmenu('cartera-menu')"
            class="group w-full flex items-center justify-between px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.cuotas-administracion.*') || request()->routeIs('admin.cartera.*') || request()->routeIs('admin.cuentas-cobro.*') || request()->routeIs('admin.recaudos.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <div class="flex items-center">
                <i class="fas fa-wallet mr-3"></i>
                <span>Cartera</span>
            </div>
            <i id="cartera-menu-icon" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
        </button>
        
        <!-- Submenú de Cartera -->
        <div id="cartera-menu" class="hidden pl-4 mt-1 space-y-1">
            @if(\App\Helpers\AdminHelper::hasPermission('cuotas-administracion.view'))
            <a 
                href="{{ route('admin.cuotas-administracion.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.cuotas-administracion.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-cog mr-3 text-xs"></i>
                Conf. Cuotas Administración
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('cartera.view'))
            <a 
                href="{{ route('admin.cartera.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.cartera.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-wallet mr-3 text-xs"></i>
                Cartera Unidades
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('cuentas-cobro.view'))
            <a 
                href="{{ route('admin.cuentas-cobro.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.cuentas-cobro.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-file-invoice-dollar mr-3 text-xs"></i>
                Cuentas de Cobro
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('recaudos.view'))
            <a 
                href="{{ route('admin.recaudos.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.recaudos.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-money-bill-wave mr-3 text-xs"></i>
                Recaudos
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('acuerdos-pagos.view'))
            <a 
                href="{{ route('admin.acuerdos-pagos.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.acuerdos-pagos.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-handshake mr-3 text-xs"></i>
                Acuerdos de Pago
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Menú Gestión con submenú -->
    @if($hasGestionPerms)
    <div class="mb-1">
        <button 
            type="button" 
            onclick="toggleSubmenu('gestion-menu')"
            class="group w-full flex items-center justify-between px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.comunicados.*') || request()->routeIs('admin.correspondencias.*') || request()->routeIs('admin.visitas.*') || request()->routeIs('admin.autorizaciones.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <div class="flex items-center">
                <i class="fas fa-tasks mr-3"></i>
                <span>Gestión</span>
            </div>
            <i id="gestion-menu-icon" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
        </button>
        
        <!-- Submenú de Gestión -->
        <div id="gestion-menu" class="hidden pl-4 mt-1 space-y-1">
            @if(\App\Helpers\AdminHelper::hasPermission('comunicados.view'))
            <a 
                href="{{ route('admin.comunicados.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.comunicados.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-bullhorn mr-3 text-xs"></i>
                Comunicados
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('correspondencias.view'))
            <a 
                href="{{ route('admin.correspondencias.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.correspondencias.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-box mr-3 text-xs"></i>
                Correspondencia
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('visitas.view'))
            <a 
                href="{{ route('admin.visitas.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.visitas.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-user-friends mr-3 text-xs"></i>
                Visitas
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('autorizaciones.view'))
            <a 
                href="{{ route('admin.autorizaciones.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.autorizaciones.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-id-card mr-3 text-xs"></i>
                Autorizaciones
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Menú Convivencia con submenú -->
    @if($hasConvivenciaPerms)
    <div class="mb-1">
        <button 
            type="button" 
            onclick="toggleSubmenu('convivencia-menu')"
            class="group w-full flex items-center justify-between px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.llamados-atencion.*') || request()->routeIs('admin.pqrs.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <div class="flex items-center">
                <i class="fas fa-users mr-3"></i>
                <span>Convivencia</span>
            </div>
            <i id="convivencia-menu-icon" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
        </button>
        
        <!-- Submenú de Convivencia -->
        <div id="convivencia-menu" class="hidden pl-4 mt-1 space-y-1">
            @if(\App\Helpers\AdminHelper::hasPermission('llamados-atencion.view'))
            <a 
                href="{{ route('admin.llamados-atencion.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.llamados-atencion.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-exclamation-triangle mr-3 text-xs"></i>
                Llamados de Atención
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('pqrs.view'))
            <a 
                href="{{ route('admin.pqrs.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.pqrs.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <i class="fas fa-comments mr-3 text-xs"></i>
                PQRS
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Menú Reservas -->
    @if(\App\Helpers\AdminHelper::hasPermission('reservas.view'))
    <div class="mb-1">
        <a 
            href="{{ route('admin.reservas.index') }}" 
            class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.reservas.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <i class="fas fa-calendar-check mr-3"></i>
            <span>Reservas</span>
        </a>
    </div>
    @endif

    <!-- Menú Sorteos Parqueaderos -->
    @if(\App\Helpers\AdminHelper::hasPermission('sorteos-parqueadero.view'))
    <div class="mb-1">
        <a 
            href="{{ route('admin.sorteos-parqueadero.index') }}" 
            class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.sorteos-parqueadero.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <i class="fas fa-car mr-3"></i>
            <span>Sorteos Parqueaderos</span>
        </a>
    </div>
    @endif

    <!-- Menú Manual de Convivencia -->
    @if(\App\Helpers\AdminHelper::hasPermission('manual-convivencia.view'))
    <div class="mb-1">
        <a 
            href="{{ route('admin.manual-convivencia.index') }}" 
            class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.manual-convivencia.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <i class="fas fa-book mr-3"></i>
            <span>Manual de Convivencia</span>
        </a>
    </div>
    @endif

    <!-- Menú Usuarios Admin -->
    @if(\App\Helpers\AdminHelper::hasPermission('usuarios-admin.view'))
    <div class="mb-1">
        <a 
            href="{{ route('admin.usuarios-admin.index') }}" 
            class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.usuarios-admin.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <i class="fas fa-user-cog mr-3"></i>
            <span>Usuarios Admin</span>
        </a>
    </div>
    @endif
    
    @if($modulos->isEmpty())
        <div class="px-2 py-4 text-sm text-gray-500">
            <i class="fas fa-info-circle mr-2"></i>
            No hay módulos activos para esta propiedad
        </div>
    @endif
@else
    <div class="px-2 py-4 text-sm text-red-500">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No hay propiedad asignada
    </div>
@endif
