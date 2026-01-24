@extends('layouts.app')

@section('title', 'Dashboard - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-2 text-sm text-gray-600">Panel de control del superadministrador</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Tarjeta de Propiedades -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <i class="fas fa-building text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Propiedades</p>
                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Propiedad::count() }}</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Planes -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <i class="fas fa-box text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Planes</p>
                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Plan::count() }}</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Administradores -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                <i class="fas fa-users text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Administradores</p>
                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\User::whereHas('roles', fn($q) => $q->where('slug', 'administrador'))->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Módulos -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <i class="fas fa-puzzle-piece text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Módulos</p>
                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Modulo::count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Accesos Rápidos</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('superadmin.propiedades.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-plus-circle text-blue-600 text-2xl mr-3"></i>
            <div>
                <p class="font-medium text-gray-900">Nueva Propiedad</p>
                <p class="text-sm text-gray-500">Crear una nueva copropiedad</p>
            </div>
        </a>
        
        <a href="{{ route('superadmin.planes.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-plus-circle text-green-600 text-2xl mr-3"></i>
            <div>
                <p class="font-medium text-gray-900">Nuevo Plan</p>
                <p class="text-sm text-gray-500">Crear un nuevo plan</p>
            </div>
        </a>
        
        <a href="{{ route('superadmin.administradores.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-user-plus text-purple-600 text-2xl mr-3"></i>
            <div>
                <p class="font-medium text-gray-900">Nuevo Administrador</p>
                <p class="text-sm text-gray-500">Crear un nuevo administrador</p>
            </div>
        </a>
    </div>
</div>
@endsection
