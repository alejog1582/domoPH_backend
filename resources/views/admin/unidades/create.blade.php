@extends('admin.layouts.app')

@section('title', 'Crear Unidad - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Unidad</h1>
            <p class="mt-2 text-sm text-gray-600">Registra una nueva unidad habitacional</p>
        </div>
        <a href="{{ route('admin.unidades.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.unidades.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Número -->
            <div>
                <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                    Número <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="numero" 
                    name="numero" 
                    value="{{ old('numero') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('numero') border-red-500 @enderror"
                >
                @error('numero')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Torre -->
            <div>
                <label for="torre" class="block text-sm font-medium text-gray-700 mb-2">Torre</label>
                <input 
                    type="text" 
                    id="torre" 
                    name="torre" 
                    value="{{ old('torre') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('torre') border-red-500 @enderror"
                >
                @error('torre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bloque -->
            <div>
                <label for="bloque" class="block text-sm font-medium text-gray-700 mb-2">Bloque</label>
                <input 
                    type="text" 
                    id="bloque" 
                    name="bloque" 
                    value="{{ old('bloque') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('bloque') border-red-500 @enderror"
                >
                @error('bloque')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo -->
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo" 
                    name="tipo" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo') border-red-500 @enderror"
                >
                    <option value="">Seleccione un tipo</option>
                    <option value="apartamento" {{ old('tipo') == 'apartamento' ? 'selected' : '' }}>Apartamento</option>
                    <option value="casa" {{ old('tipo') == 'casa' ? 'selected' : '' }}>Casa</option>
                    <option value="local" {{ old('tipo') == 'local' ? 'selected' : '' }}>Local</option>
                    <option value="parqueadero" {{ old('tipo') == 'parqueadero' ? 'selected' : '' }}>Parqueadero</option>
                    <option value="bodega" {{ old('tipo') == 'bodega' ? 'selected' : '' }}>Bodega</option>
                    <option value="otro" {{ old('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Área (m²) -->
            <div>
                <label for="area_m2" class="block text-sm font-medium text-gray-700 mb-2">Área (m²)</label>
                <input 
                    type="number" 
                    id="area_m2" 
                    name="area_m2" 
                    value="{{ old('area_m2') }}" 
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('area_m2') border-red-500 @enderror"
                >
                @error('area_m2')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Coeficiente -->
            <div>
                <label for="coeficiente" class="block text-sm font-medium text-gray-700 mb-2">Coeficiente</label>
                <input 
                    type="number" 
                    id="coeficiente" 
                    name="coeficiente" 
                    value="{{ old('coeficiente') }}" 
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('coeficiente') border-red-500 @enderror"
                >
                @error('coeficiente')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Habitaciones -->
            <div>
                <label for="habitaciones" class="block text-sm font-medium text-gray-700 mb-2">Habitaciones</label>
                <input 
                    type="number" 
                    id="habitaciones" 
                    name="habitaciones" 
                    value="{{ old('habitaciones') }}" 
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('habitaciones') border-red-500 @enderror"
                >
                @error('habitaciones')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Baños -->
            <div>
                <label for="banos" class="block text-sm font-medium text-gray-700 mb-2">Baños</label>
                <input 
                    type="number" 
                    id="banos" 
                    name="banos" 
                    value="{{ old('banos') }}" 
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('banos') border-red-500 @enderror"
                >
                @error('banos')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select 
                    id="estado" 
                    name="estado" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('estado') border-red-500 @enderror"
                >
                    <option value="">Seleccione un estado</option>
                    <option value="ocupada" {{ old('estado') == 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                    <option value="desocupada" {{ old('estado') == 'desocupada' ? 'selected' : '' }}>Desocupada</option>
                    <option value="en_construccion" {{ old('estado') == 'en_construccion' ? 'selected' : '' }}>En Construcción</option>
                    <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
            <a href="{{ route('admin.unidades.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Crear Unidad
            </button>
        </div>
    </form>
</div>
@endsection
