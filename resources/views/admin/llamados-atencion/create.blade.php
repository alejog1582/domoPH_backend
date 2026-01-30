@extends('admin.layouts.app')

@section('title', 'Crear Llamado de Atención - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Llamado de Atención</h1>
            <p class="mt-2 text-sm text-gray-600">Registrar un nuevo llamado de atención</p>
        </div>
        <a href="{{ route('admin.llamados-atencion.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.llamados-atencion.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Unidad -->
            <div>
                <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Unidad
                </label>
                <select name="unidad_id" id="unidad_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('unidad_id') border-red-500 @enderror">
                    <option value="">Seleccione (opcional)</option>
                    @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                            {{ $unidad->numero }} @if($unidad->torre) - Torre {{ $unidad->torre }} @endif @if($unidad->bloque) - Bloque {{ $unidad->bloque }} @endif
                        </option>
                    @endforeach
                </select>
                @error('unidad_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo -->
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select name="tipo" id="tipo" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="convivencia" {{ old('tipo') == 'convivencia' ? 'selected' : '' }}>Convivencia</option>
                    <option value="ruido" {{ old('tipo') == 'ruido' ? 'selected' : '' }}>Ruido</option>
                    <option value="mascotas" {{ old('tipo') == 'mascotas' ? 'selected' : '' }}>Mascotas</option>
                    <option value="parqueadero" {{ old('tipo') == 'parqueadero' ? 'selected' : '' }}>Parqueadero</option>
                    <option value="seguridad" {{ old('tipo') == 'seguridad' ? 'selected' : '' }}>Seguridad</option>
                    <option value="otro" {{ old('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Motivo -->
            <div>
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Motivo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required maxlength="200"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo') border-red-500 @enderror">
                @error('motivo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nivel -->
            <div>
                <label for="nivel" class="block text-sm font-medium text-gray-700 mb-1">
                    Nivel <span class="text-red-500">*</span>
                </label>
                <select name="nivel" id="nivel" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nivel') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="leve" {{ old('nivel') == 'leve' ? 'selected' : '' }}>Leve</option>
                    <option value="moderado" {{ old('nivel') == 'moderado' ? 'selected' : '' }}>Moderado</option>
                    <option value="grave" {{ old('nivel') == 'grave' ? 'selected' : '' }}>Grave</option>
                </select>
                @error('nivel')
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
                    <option value="">Seleccione...</option>
                    <option value="abierto" {{ old('estado', 'abierto') == 'abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="en_proceso" {{ old('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="cerrado" {{ old('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    <option value="anulado" {{ old('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Evento -->
            <div>
                <label for="fecha_evento" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y Hora del Evento <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="fecha_evento" id="fecha_evento" value="{{ old('fecha_evento', now()->format('Y-m-d\TH:i')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_evento') border-red-500 @enderror">
                @error('fecha_evento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                Descripción <span class="text-red-500">*</span>
            </label>
            <textarea name="descripcion" id="descripcion" rows="5" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Observaciones -->
        <div class="mb-4">
            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                Observaciones
            </label>
            <textarea name="observaciones" id="observaciones" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones') }}</textarea>
            @error('observaciones')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Reincidencia -->
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="es_reincidencia" id="es_reincidencia" value="1" {{ old('es_reincidencia') ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700">Es reincidencia</span>
            </label>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.llamados-atencion.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Guardar Llamado de Atención
            </button>
        </div>
    </form>
</div>
@endsection
