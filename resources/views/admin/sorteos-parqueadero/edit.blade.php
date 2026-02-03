@extends('admin.layouts.app')

@section('title', 'Editar Sorteo Parqueaderos - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Sorteo de Parqueaderos</h1>
            <p class="mt-2 text-sm text-gray-600">Modifica la información del sorteo</p>
        </div>
        <a href="{{ route('admin.sorteos-parqueadero.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.sorteos-parqueadero.update', $sorteo->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Título -->
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $sorteo->titulo) }}" required maxlength="150"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror"
                placeholder="Ej: Sorteo Parqueaderos 2026">
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                Descripción
            </label>
            <textarea name="descripcion" id="descripcion" rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $sorteo->descripcion) }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- Fecha Inicio Recolección -->
            <div>
                <label for="fecha_inicio_recoleccion" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Inicio Recolección <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_inicio_recoleccion" id="fecha_inicio_recoleccion" 
                    value="{{ old('fecha_inicio_recoleccion', $sorteo->fecha_inicio_recoleccion->format('Y-m-d')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio_recoleccion') border-red-500 @enderror">
                @error('fecha_inicio_recoleccion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Fin Recolección -->
            <div>
                <label for="fecha_fin_recoleccion" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Fin Recolección <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_fin_recoleccion" id="fecha_fin_recoleccion" 
                    value="{{ old('fecha_fin_recoleccion', $sorteo->fecha_fin_recoleccion->format('Y-m-d')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_fin_recoleccion') border-red-500 @enderror">
                @error('fecha_fin_recoleccion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Sorteo -->
            <div>
                <label for="fecha_sorteo" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Sorteo <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_sorteo" id="fecha_sorteo" 
                    value="{{ old('fecha_sorteo', $sorteo->fecha_sorteo->format('Y-m-d')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_sorteo') border-red-500 @enderror">
                @error('fecha_sorteo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select name="estado" id="estado" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estado') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="borrador" {{ old('estado', $sorteo->estado) == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="activo" {{ old('estado', $sorteo->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="cerrado" {{ old('estado', $sorteo->estado) == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    <option value="anulado" {{ old('estado', $sorteo->estado) == 'anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Activo -->
            <div class="flex items-center">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="activo" id="activo" value="1" 
                        {{ old('activo', $sorteo->activo) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                </div>
                <label for="activo" class="ml-2 block text-sm text-gray-700">
                    Sorteo Activo
                </label>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.sorteos-parqueadero.index') }}" 
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>Actualizar Sorteo
            </button>
        </div>
    </form>
</div>
@endsection
