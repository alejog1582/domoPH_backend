@extends('admin.layouts.app')

@section('title', 'Editar Depósito - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Depósito</h1>
            <p class="mt-2 text-sm text-gray-600">Modifica la información del depósito</p>
        </div>
        <a href="{{ route('admin.depositos.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.depositos.update', $deposito->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Código -->
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                    Código <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="codigo" 
                    name="codigo" 
                    value="{{ old('codigo', $deposito->codigo) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                >
                @error('codigo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nivel -->
            <div>
                <label for="nivel" class="block text-sm font-medium text-gray-700 mb-2">Nivel</label>
                <input 
                    type="text" 
                    id="nivel" 
                    name="nivel" 
                    value="{{ old('nivel', $deposito->nivel) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nivel') border-red-500 @enderror"
                >
                @error('nivel')
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
                    <option value="disponible" {{ old('estado', $deposito->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="asignado" {{ old('estado', $deposito->estado) == 'asignado' ? 'selected' : '' }}>Asignado</option>
                    <option value="en_mantenimiento" {{ old('estado', $deposito->estado) == 'en_mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                    <option value="inhabilitado" {{ old('estado', $deposito->estado) == 'inhabilitado' ? 'selected' : '' }}>Inhabilitado</option>
                </select>
                @error('estado')
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
                    value="{{ old('area_m2', $deposito->area_m2) }}" 
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('area_m2') border-red-500 @enderror"
                >
                @error('area_m2')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Unidad -->
            <div>
                <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-2">Unidad Asignada</label>
                <select 
                    id="unidad_id" 
                    name="unidad_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('unidad_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione una unidad (opcional)</option>
                    @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id }}" {{ old('unidad_id', $deposito->unidad_id) == $unidad->id ? 'selected' : '' }}>
                            {{ $unidad->numero }} @if($unidad->torre) - Torre {{ $unidad->torre }} @endif @if($unidad->bloque) - Bloque {{ $unidad->bloque }} @endif
                        </option>
                    @endforeach
                </select>
                @error('unidad_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Residente Responsable -->
            <div>
                <label for="residente_responsable_id" class="block text-sm font-medium text-gray-700 mb-2">Residente Responsable</label>
                <select 
                    id="residente_responsable_id" 
                    name="residente_responsable_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('residente_responsable_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione un residente (opcional)</option>
                    @foreach($residentes as $residente)
                        <option value="{{ $residente->id }}" {{ old('residente_responsable_id', $deposito->residente_responsable_id) == $residente->id ? 'selected' : '' }}>
                            {{ $residente->user->nombre ?? 'N/A' }} - {{ $residente->user->documento_identidad ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('residente_responsable_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Asignación -->
            <div>
                <label for="fecha_asignacion" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Asignación</label>
                <input 
                    type="date" 
                    id="fecha_asignacion" 
                    name="fecha_asignacion" 
                    value="{{ old('fecha_asignacion', $deposito->fecha_asignacion ? $deposito->fecha_asignacion->format('Y-m-d') : '') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_asignacion') border-red-500 @enderror"
                >
                @error('fecha_asignacion')
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
                >{{ old('observaciones', $deposito->observaciones) }}</textarea>
                @error('observaciones')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.depositos.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Actualizar Depósito
            </button>
        </div>
    </form>
</div>
@endsection
