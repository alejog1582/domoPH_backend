@extends('admin.layouts.app')

@section('title', 'Crear Sorteo Parqueaderos - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Sorteo de Parqueaderos</h1>
            <p class="mt-2 text-sm text-gray-600">Crea un nuevo sorteo de parqueaderos para la propiedad</p>
        </div>
        <a href="{{ route('admin.sorteos-parqueadero.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.sorteos-parqueadero.store') }}" method="POST">
        @csrf

        <!-- Título -->
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required maxlength="150"
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
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
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
                    value="{{ old('fecha_inicio_recoleccion') }}" required
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
                    value="{{ old('fecha_fin_recoleccion') }}" required
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
                    value="{{ old('fecha_sorteo') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_sorteo') border-red-500 @enderror">
                @error('fecha_sorteo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Hora Sorteo -->
            <div>
                <label for="hora_sorteo" class="block text-sm font-medium text-gray-700 mb-1">
                    Hora Sorteo
                </label>
                <input type="time" name="hora_sorteo" id="hora_sorteo" 
                    value="{{ old('hora_sorteo') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('hora_sorteo') border-red-500 @enderror"
                    placeholder="HH:MM">
                <p class="mt-1 text-xs text-gray-500">Hora en que se realizará el sorteo</p>
                @error('hora_sorteo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Fecha Inicio Uso -->
            <div>
                <label for="fecha_inicio_uso" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Inicio de Uso
                </label>
                <input type="date" name="fecha_inicio_uso" id="fecha_inicio_uso" 
                    value="{{ old('fecha_inicio_uso') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio_uso') border-red-500 @enderror"
                    placeholder="Fecha desde cuando pueden usar el parqueadero">
                <p class="mt-1 text-xs text-gray-500">Fecha desde la cual los ganadores pueden comenzar a usar el parqueadero asignado</p>
                @error('fecha_inicio_uso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Duración en Meses -->
            <div>
                <label for="duracion_meses" class="block text-sm font-medium text-gray-700 mb-1">
                    Duración (Meses)
                </label>
                <input type="number" name="duracion_meses" id="duracion_meses" 
                    value="{{ old('duracion_meses') }}" min="1" step="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('duracion_meses') border-red-500 @enderror"
                    placeholder="Cantidad de meses de asignación">
                <p class="mt-1 text-xs text-gray-500">Cantidad de meses que dura la asignación del parqueadero</p>
                @error('duracion_meses')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Capacidad Autos -->
            <div>
                <label for="capacidad_autos" class="block text-sm font-medium text-gray-700 mb-1">
                    Capacidad de Autos <span class="text-red-500">*</span>
                </label>
                <input type="number" name="capacidad_autos" id="capacidad_autos" 
                    value="{{ old('capacidad_autos', 0) }}" required min="0" step="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('capacidad_autos') border-red-500 @enderror"
                    placeholder="Número de parqueaderos para autos">
                @error('capacidad_autos')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacidad Motos -->
            <div>
                <label for="capacidad_motos" class="block text-sm font-medium text-gray-700 mb-1">
                    Capacidad de Motos <span class="text-red-500">*</span>
                </label>
                <input type="number" name="capacidad_motos" id="capacidad_motos" 
                    value="{{ old('capacidad_motos', 0) }}" required min="0" step="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('capacidad_motos') border-red-500 @enderror"
                    placeholder="Número de parqueaderos para motos">
                @error('capacidad_motos')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Balotas Blancas Carro -->
            <div>
                <label for="balotas_blancas_carro" class="block text-sm font-medium text-gray-700 mb-1">
                    Balotas Blancas Carro
                </label>
                <input type="number" name="balotas_blancas_carro" id="balotas_blancas_carro" 
                    value="{{ old('balotas_blancas_carro', 0) }}" min="0" step="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('balotas_blancas_carro') border-red-500 @enderror"
                    placeholder="Número de balotas blancas para carros">
                <p class="mt-1 text-xs text-gray-500">Número de balotas blancas (no favorecidos) para carros</p>
                @error('balotas_blancas_carro')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Balotas Blancas Moto -->
            <div>
                <label for="balotas_blancas_moto" class="block text-sm font-medium text-gray-700 mb-1">
                    Balotas Blancas Moto
                </label>
                <input type="number" name="balotas_blancas_moto" id="balotas_blancas_moto" 
                    value="{{ old('balotas_blancas_moto', 0) }}" min="0" step="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('balotas_blancas_moto') border-red-500 @enderror"
                    placeholder="Número de balotas blancas para motos">
                <p class="mt-1 text-xs text-gray-500">Número de balotas blancas (no favorecidos) para motos</p>
                @error('balotas_blancas_moto')
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
                    <option value="borrador" {{ old('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="cerrado" {{ old('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    <option value="anulado" {{ old('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Activo -->
            <div class="flex items-center">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="activo" id="activo" value="1" 
                        {{ old('activo', true) ? 'checked' : '' }}
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
                <i class="fas fa-save mr-2"></i>Guardar Sorteo
            </button>
        </div>
    </form>
</div>
@endsection
