@extends('admin.layouts.app')

@section('title', 'Dashboard - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
    $modulos = \App\Helpers\AdminHelper::getModulosActivosConPermisos();
@endphp

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-2 text-sm text-gray-600">Bienvenido, {{ Auth::user()->nombre }}</p>
</div>

@if($propiedad)
    <!-- Información de la Propiedad -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-building mr-2"></i> Propiedad: {{ $propiedad->nombre }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-500">Dirección</p>
                <p class="text-sm font-medium text-gray-900">{{ $propiedad->direccion }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Ciudad</p>
                <p class="text-sm font-medium text-gray-900">{{ $propiedad->ciudad }}, {{ $propiedad->departamento }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $propiedad->estado == 'activa' ? 'bg-green-100 text-green-800' : 
                       ($propiedad->estado == 'suspendida' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ucfirst($propiedad->estado) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Módulos Activos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-puzzle-piece mr-2"></i> Módulos Activos
        </h2>
        
        @if($modulos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modulos as $modulo)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center mb-2">
                            @if($modulo->icono)
                                <i class="fas fa-{{ $modulo->icono }} text-blue-600 text-2xl mr-3"></i>
                            @else
                                <i class="fas fa-circle text-blue-600 text-2xl mr-3"></i>
                            @endif
                            <h3 class="text-lg font-semibold text-gray-900">{{ $modulo->nombre }}</h3>
                        </div>
                        @if($modulo->descripcion)
                            <p class="text-sm text-gray-600 mb-3">{{ $modulo->descripcion }}</p>
                        @endif
                        @if($modulo->ruta)
                            <a href="{{ $modulo->ruta }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Acceder <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">No hay módulos activos para esta propiedad</p>
            </div>
        @endif
    </div>
@else
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>No tienes propiedades asignadas. Contacta al superadministrador.</span>
        </div>
    </div>
@endif
@endsection
