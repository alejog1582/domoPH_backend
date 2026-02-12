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
    
    <!-- domoPH Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/domoph-styles.css') }}">
    
    @stack('styles')
</head>
<body class="bg-domoph">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="navbar-domoph">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="flex justify-end items-center h-16">
                    <div class="flex items-center space-x-4">
                        @php
                            $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
                        @endphp
                        @if($propiedad)
                            <span class="text-sm">
                                <i class="fas fa-building mr-1"></i>
                                {{ $propiedad->nombre }}
                            </span>
                        @endif
                        <span>{{ Auth::user()->nombre }}</span>
                        <form method="POST" action="{{ route('admin.logout') }}">
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
                    @include('admin.layouts.partials.sidebar')
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
    
    @include('admin.layouts.partials.sidebar-scripts')
</body>
</html>
