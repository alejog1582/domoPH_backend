@extends('admin.layouts.app')

@section('title', 'Ver Categoría Ecommerce - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalles de la Categoría</h1>
            <p class="mt-2 text-sm text-gray-600">Información completa de la categoría</p>
        </div>
        <div class="flex items-center space-x-2">
            @if(\App\Helpers\AdminHelper::hasPermission('ecommerce-categorias.edit'))
            <a href="{{ route('admin.ecommerce-categorias.edit', $categoria->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            @endif
            <a href="{{ route('admin.ecommerce-categorias.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nombre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
            <p class="text-lg font-semibold text-gray-900">{{ $categoria->nombre }}</p>
        </div>

        <!-- Slug -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
            <p class="text-sm text-gray-600 font-mono">{{ $categoria->slug }}</p>
        </div>

        <!-- Icono -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Icono</label>
            @if($categoria->icono)
                <p class="text-sm text-gray-900">
                    <i class="fas fa-{{ $categoria->icono }} mr-2"></i>
                    {{ $categoria->icono }}
                </p>
            @else
                <p class="text-sm text-gray-500">No especificado</p>
            @endif
        </div>

        <!-- Estado -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
            @if($categoria->activo)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Activa
                </span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    Inactiva
                </span>
            @endif
        </div>

        <!-- Descripción -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
            <p class="text-sm text-gray-900">{{ $categoria->descripcion ?? 'Sin descripción' }}</p>
        </div>

        <!-- Fechas -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Creación</label>
            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($categoria->created_at)->format('d/m/Y H:i') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Última Actualización</label>
            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($categoria->updated_at)->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection
