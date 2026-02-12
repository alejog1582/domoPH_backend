<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'domoPH - Panel Superadministrador')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- domoPH Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/domoph-styles.css') }}">
    
    @stack('styles')
</head>
<body class="bg-domoph">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="navbar-domoph">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('superadmin.dashboard') }}" class="flex items-center">
                            <i class="fas fa-building text-primary-domoph text-2xl mr-2"></i>
                            <span class="text-xl font-bold text-gradient">domoPH</span>
                            <span class="ml-2 text-sm text-muted-domoph">SuperAdmin</span>
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span>{{ Auth::user()->nombre }}</span>
                        <form method="POST" action="{{ route('superadmin.logout') }}">
                            @csrf
                            <button type="submit" class="hover:opacity-80 transition-opacity">
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
            <aside class="w-64 sidebar-domoph shadow-lg">
                <nav class="mt-5 px-2">
                    <a href="{{ route('superadmin.dashboard') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home mr-3"></i>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('superadmin.propiedades.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.propiedades.*') ? 'active' : '' }}">
                        <i class="fas fa-building mr-3"></i>
                        Propiedades
                    </a>
                    
                    <a href="{{ route('superadmin.planes.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.planes.*') ? 'active' : '' }}">
                        <i class="fas fa-box mr-3"></i>
                        Planes
                    </a>
                    
                    <a href="{{ route('superadmin.modulos.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.modulos.*') ? 'active' : '' }}">
                        <i class="fas fa-puzzle-piece mr-3"></i>
                        Módulos
                    </a>
                    
                    <a href="{{ route('superadmin.administradores.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.administradores.*') ? 'active' : '' }}">
                        <i class="fas fa-users mr-3"></i>
                        Administradores
                    </a>
                    
                    <a href="{{ route('superadmin.configuraciones.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.configuraciones.*') ? 'active' : '' }}">
                        <i class="fas fa-cog mr-3"></i>
                        Configuraciones
                    </a>
                    
                    <a href="{{ route('superadmin.auditoria.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.auditoria.*') ? 'active' : '' }}">
                        <i class="fas fa-history mr-3"></i>
                        Auditoría
                    </a>
                    
                    <a href="{{ route('superadmin.solicitudes-comerciales.index') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('superadmin.solicitudes-comerciales.*') ? 'active' : '' }}">
                        <i class="fas fa-handshake mr-3"></i>
                        Solicitudes Comerciales
                    </a>
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
</body>
</html>
