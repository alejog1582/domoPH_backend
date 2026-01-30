@extends('admin.layouts.app')

@section('title', 'Crear Residente - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Residente</h1>
            <p class="mt-2 text-sm text-gray-600">Registra un nuevo residente</p>
        </div>
        <a href="{{ route('admin.residentes.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.residentes.store') }}" method="POST">
        @csrf

        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Usuario</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    value="{{ old('nombre') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror"
                >
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Documento -->
            <div>
                <label for="documento_identidad" class="block text-sm font-medium text-gray-700 mb-2">
                    Documento de Identidad <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="documento_identidad" 
                    name="documento_identidad" 
                    value="{{ old('documento_identidad') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('documento_identidad') border-red-500 @enderror"
                >
                @error('documento_identidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono -->
            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                    Teléfono <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="telefono" 
                    name="telefono" 
                    value="{{ old('telefono') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('telefono') border-red-500 @enderror"
                >
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Información del Residente</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Unidad -->
            <div>
                <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Unidad <span class="text-red-500">*</span>
                </label>
                <select 
                    id="unidad_id" 
                    name="unidad_id" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('unidad_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione una unidad</option>
                    @foreach($unidades as $unidad)
                        @php
                            $unidadLabel = $unidad->numero;
                            if ($unidad->torre) $unidadLabel .= ' - ' . $unidad->torre;
                            if ($unidad->bloque) $unidadLabel .= ' - ' . $unidad->bloque;
                        @endphp
                        <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                            {{ $unidadLabel }}
                        </option>
                    @endforeach
                </select>
                @error('unidad_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Relación -->
            <div>
                <label for="tipo_relacion" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Relación <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo_relacion" 
                    name="tipo_relacion" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo_relacion') border-red-500 @enderror"
                >
                    <option value="">Seleccione un tipo</option>
                    <option value="propietario" {{ old('tipo_relacion') == 'propietario' ? 'selected' : '' }}>Propietario</option>
                    <option value="arrendatario" {{ old('tipo_relacion') == 'arrendatario' ? 'selected' : '' }}>Arrendatario</option>
                    <option value="residente_temporal" {{ old('tipo_relacion') == 'residente_temporal' ? 'selected' : '' }}>Residente Temporal</option>
                    <option value="otro" {{ old('tipo_relacion') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo_relacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Inicio</label>
                <input 
                    type="date" 
                    id="fecha_inicio" 
                    name="fecha_inicio" 
                    value="{{ old('fecha_inicio') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_inicio') border-red-500 @enderror"
                >
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Fin -->
            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Fin</label>
                <input 
                    type="date" 
                    id="fecha_fin" 
                    name="fecha_fin" 
                    value="{{ old('fecha_fin') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_fin') border-red-500 @enderror"
                >
                @error('fecha_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Es Principal -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="es_principal" 
                        value="1"
                        {{ old('es_principal') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Residente Principal</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Marca esta opción si este residente es el principal de la unidad.</p>
            </div>

            <!-- Recibe Notificaciones -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="recibe_notificaciones" 
                        value="1"
                        {{ old('recibe_notificaciones', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Recibe Notificaciones</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Marca esta opción si el residente debe recibir notificaciones.</p>
            </div>

            <!-- Observaciones -->
            <div class="md:col-span-2">
                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                <textarea 
                    id="observaciones" 
                    name="observaciones" 
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('observaciones') border-red-500 @enderror"
                >{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.residentes.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Crear Residente
            </button>
        </div>
    </form>
</div>
@endsection
