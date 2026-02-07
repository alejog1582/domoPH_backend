@extends('admin.layouts.app')

@section('title', 'Editar Encuesta - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Encuesta</h1>
            <p class="mt-2 text-sm text-gray-600">Edita la información de la encuesta</p>
        </div>
        <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'encuestas']) }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.encuestas-votaciones.update', $encuesta->id) }}" method="POST" id="formEncuestaVotacion">
        @csrf
        @method('PUT')
        <input type="hidden" name="tipo" value="encuesta">

        <!-- Título -->
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $encuesta->titulo) }}" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror"
                placeholder="Ej: Encuesta sobre mejoras en zonas comunes">
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                Descripción
            </label>
            <textarea name="descripcion" id="descripcion" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror"
                placeholder="Describe el propósito de la encuesta">{{ old('descripcion', $encuesta->descripcion) }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Las encuestas siempre son de respuesta abierta -->
        <input type="hidden" name="tipo_respuesta" value="respuesta_abierta">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Fecha Inicio -->
            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Inicio <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" 
                    value="{{ old('fecha_inicio', $encuesta->fecha_inicio->format('Y-m-d')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio') border-red-500 @enderror">
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Fin -->
            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Fin <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_fin" id="fecha_fin" 
                    value="{{ old('fecha_fin', $encuesta->fecha_fin->format('Y-m-d')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_fin') border-red-500 @enderror">
                @error('fecha_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select name="estado" id="estado" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estado') border-red-500 @enderror">
                    <option value="activa" {{ old('estado', $encuesta->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="cerrada" {{ old('estado', $encuesta->estado) == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    <option value="anulada" {{ old('estado', $encuesta->estado) == 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-4 mt-6">
            <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'encuestas']) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Actualizar
            </button>
        </div>
    </form>
</div>
@endsection
