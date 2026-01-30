<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'domoPH - Panel Administrador')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            <i class="fas fa-building text-blue-600 text-2xl mr-2"></i>
                            <span class="text-xl font-bold text-gray-800">domoPH</span>
                            <span class="ml-2 text-sm text-gray-500">Admin</span>
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @php
                            $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
                        @endphp
                        @if($propiedad)
                            <span class="text-gray-700 text-sm">
                                <i class="fas fa-building mr-1"></i>
                                {{ $propiedad->nombre }}
                            </span>
                        @endif
                        <span class="text-gray-700">{{ Auth::user()->nombre }}</span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt mr-1"></i> Salir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar y Contenido -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-64 bg-white shadow-lg">
                <nav class="mt-5 px-2">
                    @php
                        $modulos = \App\Helpers\AdminHelper::getModulosActivos();
                        $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
                    @endphp

                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i class="fas fa-home mr-3"></i>
                        Dashboard
                    </a>

                    @if($propiedad)
                        <!-- Menú Copropiedad con submenú -->
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
                                <a 
                                    href="{{ route('admin.unidades.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.unidades.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-door-open mr-3 text-xs"></i>
                                    Unidades
                                </a>
                                <a 
                                    href="{{ route('admin.residentes.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.residentes.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-users mr-3 text-xs"></i>
                                    Residentes
                                </a>
                                <a 
                                    href="{{ route('admin.mascotas.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.mascotas.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-paw mr-3 text-xs"></i>
                                    Mascotas
                                </a>
                                <a 
                                    href="{{ route('admin.zonas-sociales.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.zonas-sociales.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-swimming-pool mr-3 text-xs"></i>
                                    Zonas Comunes
                                </a>
                                <!-- Aquí se pueden agregar más opciones del submenú -->
                            </div>
                        </div>

                        <!-- Menú Cartera con submenú -->
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
                                <a 
                                    href="{{ route('admin.cuotas-administracion.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.cuotas-administracion.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-cog mr-3 text-xs"></i>
                                    Conf. Cuotas Administración
                                </a>
                                <a 
                                    href="{{ route('admin.cartera.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.cartera.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-wallet mr-3 text-xs"></i>
                                    Cartera Unidades
                                </a>
                                <a 
                                    href="{{ route('admin.cuentas-cobro.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.cuentas-cobro.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-file-invoice-dollar mr-3 text-xs"></i>
                                    Cuentas de Cobro
                                </a>
                                <a 
                                    href="{{ route('admin.recaudos.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.recaudos.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-money-bill-wave mr-3 text-xs"></i>
                                    Recaudos
                                </a>
                                <a 
                                    href="{{ route('admin.acuerdos-pagos.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.acuerdos-pagos.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-handshake mr-3 text-xs"></i>
                                    Acuerdos de Pago
                                </a>
                                <!-- Aquí se pueden agregar más opciones del submenú -->
                            </div>
                        </div>

                        <!-- Menú Gestión con submenú -->
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
                                <a 
                                    href="{{ route('admin.comunicados.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.comunicados.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-bullhorn mr-3 text-xs"></i>
                                    Comunicados
                                </a>
                                <a 
                                    href="{{ route('admin.correspondencias.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.correspondencias.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-box mr-3 text-xs"></i>
                                    Correspondencia
                                </a>
                                <a 
                                    href="{{ route('admin.visitas.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.visitas.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-user-friends mr-3 text-xs"></i>
                                    Visitas
                                </a>
                                <a 
                                    href="{{ route('admin.autorizaciones.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.autorizaciones.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-id-card mr-3 text-xs"></i>
                                    Autorizaciones
                                </a>
                            </div>
                        </div>

                        <!-- Menú Convivencia con submenú -->
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
                                <a 
                                    href="{{ route('admin.llamados-atencion.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.llamados-atencion.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-exclamation-triangle mr-3 text-xs"></i>
                                    Llamados de Atención
                                </a>
                                <a 
                                    href="{{ route('admin.pqrs.index') }}" 
                                    class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.pqrs.*') ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    <i class="fas fa-comments mr-3 text-xs"></i>
                                    PQRS
                                </a>
                            </div>
                        </div>

                        <!-- Menú Reservas -->
                        <div class="mb-1">
                            <a 
                                href="{{ route('admin.reservas.index') }}" 
                                class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.reservas.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                            >
                                <i class="fas fa-calendar-check mr-3"></i>
                                <span>Reservas</span>
                            </a>
                        </div>

                        <!-- Módulos activos de la propiedad -->
                        <!-- @foreach($modulos as $modulo)
                            <a href="{{ $modulo->ruta ?? '#' }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->is(trim($modulo->ruta, '/')) ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                @if($modulo->icono)
                                    <i class="fas fa-{{ $modulo->icono }} mr-3"></i>
                                @else
                                    <i class="fas fa-circle mr-3"></i>
                                @endif
                                {{ $modulo->nombre }}
                            </a>
                        @endforeach -->

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
                </nav>
            </aside>

            <!-- Contenido Principal -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    
    <script>
        // Función para alternar submenús
        function toggleSubmenu(menuId) {
            const menu = document.getElementById(menuId);
            const icon = document.getElementById(menuId + '-icon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            } else {
                menu.classList.add('hidden');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        }

        // Mantener abierto el submenú si la ruta activa está dentro de él
        document.addEventListener('DOMContentLoaded', function() {
            @if(request()->routeIs('admin.unidades.*') || request()->routeIs('admin.copropiedad.*') || request()->routeIs('admin.residentes.*') || request()->routeIs('admin.mascotas.*') || request()->routeIs('admin.zonas-sociales.*'))
                const copropiedadMenu = document.getElementById('copropiedad-menu');
                const copropiedadIcon = document.getElementById('copropiedad-menu-icon');
                if (copropiedadMenu) {
                    copropiedadMenu.classList.remove('hidden');
                    if (copropiedadIcon) {
                        copropiedadIcon.classList.remove('fa-chevron-down');
                        copropiedadIcon.classList.add('fa-chevron-up');
                    }
                }
            @endif
            
            @if(request()->routeIs('admin.cuotas-administracion.*') || request()->routeIs('admin.cartera.*') || request()->routeIs('admin.cuentas-cobro.*') || request()->routeIs('admin.recaudos.*') || request()->routeIs('admin.acuerdos-pagos.*'))
                const carteraMenu = document.getElementById('cartera-menu');
                const carteraIcon = document.getElementById('cartera-menu-icon');
                if (carteraMenu) {
                    carteraMenu.classList.remove('hidden');
                    if (carteraIcon) {
                        carteraIcon.classList.remove('fa-chevron-down');
                        carteraIcon.classList.add('fa-chevron-up');
                    }
                }
            @endif

            @if(request()->routeIs('admin.comunicados.*') || request()->routeIs('admin.correspondencias.*') || request()->routeIs('admin.visitas.*') || request()->routeIs('admin.autorizaciones.*'))
                const gestionMenu = document.getElementById('gestion-menu');
                const gestionIcon = document.getElementById('gestion-menu-icon');
                if (gestionMenu) {
                    gestionMenu.classList.remove('hidden');
                    if (gestionIcon) {
                        gestionIcon.classList.remove('fa-chevron-down');
                        gestionIcon.classList.add('fa-chevron-up');
                    }
                }
            @endif

            @if(request()->routeIs('admin.llamados-atencion.*') || request()->routeIs('admin.pqrs.*'))
                const convivenciaMenu = document.getElementById('convivencia-menu');
                const convivenciaIcon = document.getElementById('convivencia-menu-icon');
                if (convivenciaMenu) {
                    convivenciaMenu.classList.remove('hidden');
                    if (convivenciaIcon) {
                        convivenciaIcon.classList.remove('fa-chevron-down');
                        convivenciaIcon.classList.add('fa-chevron-up');
                    }
                }
            @endif
        });
    </script>
</body>
</html>
