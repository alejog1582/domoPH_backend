@extends('layouts.app')

@section('title', 'Editar Plan - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Editar Plan</h1>
    <p class="mt-2 text-sm text-gray-600">Modifica la información del plan</p>
</div>

<form action="{{ route('superadmin.planes.update', ['plan' => $plan->id]) }}" method="POST" class="bg-white rounded-lg shadow p-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información Básica -->
        <div class="md:col-span-2">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Información Básica</h2>
        </div>

        <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                Nombre del Plan <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="nombre" 
                name="nombre" 
                value="{{ old('nombre', $plan->nombre) }}" 
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('nombre')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                Slug <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="slug" 
                name="slug" 
                value="{{ old('slug', $plan->slug) }}" 
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="plan-base-domoph"
            >
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                Descripción
            </label>
            <textarea 
                id="descripcion" 
                name="descripcion" 
                rows="3"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >{{ old('descripcion', $plan->descripcion) }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Precios -->
        <div class="md:col-span-2 mt-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Precios</h2>
        </div>

        <div>
            <label for="precio_mensual" class="block text-sm font-medium text-gray-700 mb-2">
                Precio Mensual (COP) <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                id="precio_mensual" 
                name="precio_mensual" 
                value="{{ old('precio_mensual', $plan->precio_mensual) }}" 
                step="0.01"
                min="0"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('precio_mensual')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="precio_anual" class="block text-sm font-medium text-gray-700 mb-2">
                Precio Anual (COP)
            </label>
            <input 
                type="number" 
                id="precio_anual" 
                name="precio_anual" 
                value="{{ old('precio_anual', $plan->precio_anual) }}" 
                step="0.01"
                min="0"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('precio_anual')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Límites -->
        <div class="md:col-span-2 mt-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Límites del Plan</h2>
        </div>

        <div>
            <label for="max_unidades" class="block text-sm font-medium text-gray-700 mb-2">
                Máximo de Unidades
            </label>
            <input 
                type="number" 
                id="max_unidades" 
                name="max_unidades" 
                value="{{ old('max_unidades', $plan->max_unidades) }}" 
                min="0"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Dejar vacío para ilimitado"
            >
            <p class="mt-1 text-xs text-gray-500">Dejar vacío para ilimitado</p>
            @error('max_unidades')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="max_usuarios" class="block text-sm font-medium text-gray-700 mb-2">
                Máximo de Usuarios
            </label>
            <input 
                type="number" 
                id="max_usuarios" 
                name="max_usuarios" 
                value="{{ old('max_usuarios', $plan->max_usuarios) }}" 
                min="0"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Dejar vacío para ilimitado"
            >
            <p class="mt-1 text-xs text-gray-500">Dejar vacío para ilimitado</p>
            @error('max_usuarios')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="max_almacenamiento_mb" class="block text-sm font-medium text-gray-700 mb-2">
                Almacenamiento Máximo (MB)
            </label>
            <input 
                type="number" 
                id="max_almacenamiento_mb" 
                name="max_almacenamiento_mb" 
                value="{{ old('max_almacenamiento_mb', $plan->max_almacenamiento_mb) }}" 
                min="0"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Dejar vacío para ilimitado"
            >
            <p class="mt-1 text-xs text-gray-500">Dejar vacío para ilimitado (ej: 10240 = 10 GB)</p>
            @error('max_almacenamiento_mb')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Configuración -->
        <div class="md:col-span-2 mt-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Configuración</h2>
        </div>

        <div>
            <label for="orden" class="block text-sm font-medium text-gray-700 mb-2">
                Orden de Visualización
            </label>
            <input 
                type="number" 
                id="orden" 
                name="orden" 
                value="{{ old('orden', $plan->orden) }}" 
                min="0"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('orden')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-4">
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="activo" 
                    name="activo" 
                    value="1"
                    {{ old('activo', $plan->activo) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="activo" class="ml-2 block text-sm text-gray-900">
                    Plan Activo
                </label>
            </div>

            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="soporte_prioritario" 
                    name="soporte_prioritario" 
                    value="1"
                    {{ old('soporte_prioritario', $plan->soporte_prioritario) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="soporte_prioritario" class="ml-2 block text-sm text-gray-900">
                    Soporte Prioritario
                </label>
            </div>
        </div>

        <!-- Módulos -->
        <div class="md:col-span-2 mt-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Módulos Incluidos</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($modulos as $modulo)
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="modulo_{{ $modulo->id }}" 
                            name="modulos[]" 
                            value="{{ $modulo->id }}"
                            {{ in_array($modulo->id, $modulosSeleccionados) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="modulo_{{ $modulo->id }}" class="ml-2 block text-sm text-gray-900">
                            {{ $modulo->nombre }}
                        </label>
                    </div>
                @endforeach
            </div>
            @if($modulos->isEmpty())
                <p class="text-sm text-gray-500">No hay módulos disponibles. Crea módulos primero.</p>
            @endif
        </div>

        <!-- Características (JSON) -->
        <div class="md:col-span-2 mt-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Características (JSON)</h2>
            <label for="caracteristicas" class="block text-sm font-medium text-gray-700 mb-2">
                Configuración JSON de características
            </label>
            <textarea 
                id="caracteristicas" 
                name="caracteristicas" 
                rows="8"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                placeholder='{"modelo_cobro": "por_unidad", "precio_por_unidad": 2000, "modulos": [...], "beneficios": [...]}'
            >{{ old('caracteristicas', $plan->caracteristicas ? json_encode($plan->caracteristicas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Ingresa un objeto JSON válido con las características del plan</p>
            @error('caracteristicas')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-4">
        <a href="{{ route('superadmin.planes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Cancelar
        </a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-save mr-2"></i> Guardar Cambios
        </button>
    </div>
</form>
@endsection
