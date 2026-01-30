@extends('admin.layouts.app')

@section('title', 'Gestionar PQRS - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestionar PQRS</h1>
            <p class="mt-2 text-sm text-gray-600">Número de Radicado: <strong>{{ $pqrs->numero_radicado }}</strong></p>
        </div>
        <a href="{{ route('admin.pqrs.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Información Principal (Solo Lectura) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información Principal</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ ucfirst($pqrs->tipo) }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ ucfirst($pqrs->categoria) }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $pqrs->asunto }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900 whitespace-pre-wrap">
                    {{ $pqrs->descripcion }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    @if($pqrs->unidad)
                        {{ $pqrs->unidad->numero }} 
                        @if($pqrs->unidad->torre) - Torre {{ $pqrs->unidad->torre }} @endif 
                        @if($pqrs->unidad->bloque) - Bloque {{ $pqrs->unidad->bloque }} @endif
                    @else
                        General
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Canal</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ ucfirst($pqrs->canal) }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Radicación</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $pqrs->fecha_radicacion->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Gestión (Editable) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Gestión</h2>
        <form action="{{ route('admin.pqrs.update', $pqrs) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <!-- Prioridad -->
                <div>
                    <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-1">
                        Prioridad <span class="text-red-500">*</span>
                    </label>
                    <select name="prioridad" id="prioridad" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('prioridad') border-red-500 @enderror">
                        <option value="baja" {{ old('prioridad', $pqrs->prioridad) == 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="media" {{ old('prioridad', $pqrs->prioridad) == 'media' ? 'selected' : '' }}>Media</option>
                        <option value="alta" {{ old('prioridad', $pqrs->prioridad) == 'alta' ? 'selected' : '' }}>Alta</option>
                        <option value="critica" {{ old('prioridad', $pqrs->prioridad) == 'critica' ? 'selected' : '' }}>Crítica</option>
                    </select>
                    @error('prioridad')
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
                        <option value="radicada" {{ old('estado', $pqrs->estado) == 'radicada' ? 'selected' : '' }}>Radicada</option>
                        <option value="en_proceso" {{ old('estado', $pqrs->estado) == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="respondida" {{ old('estado', $pqrs->estado) == 'respondida' ? 'selected' : '' }}>Respondida</option>
                        <option value="cerrada" {{ old('estado', $pqrs->estado) == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                        <option value="rechazada" {{ old('estado', $pqrs->estado) == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                    </select>
                    @error('estado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Respuesta -->
                <div>
                    <label for="respuesta" class="block text-sm font-medium text-gray-700 mb-1">
                        Respuesta
                    </label>
                    <textarea name="respuesta" id="respuesta" rows="6"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('respuesta') border-red-500 @enderror">{{ old('respuesta', $pqrs->respuesta) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Ingrese la respuesta o solución a la PQRS</p>
                    @error('respuesta')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Calificación de Servicio -->
                <div>
                    <label for="calificacion_servicio" class="block text-sm font-medium text-gray-700 mb-1">
                        Calificación del Servicio
                    </label>
                    <select name="calificacion_servicio" id="calificacion_servicio"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('calificacion_servicio') border-red-500 @enderror">
                        <option value="">Sin calificar</option>
                        <option value="1" {{ old('calificacion_servicio', $pqrs->calificacion_servicio) == 1 ? 'selected' : '' }}>1 - Muy Malo</option>
                        <option value="2" {{ old('calificacion_servicio', $pqrs->calificacion_servicio) == 2 ? 'selected' : '' }}>2 - Malo</option>
                        <option value="3" {{ old('calificacion_servicio', $pqrs->calificacion_servicio) == 3 ? 'selected' : '' }}>3 - Regular</option>
                        <option value="4" {{ old('calificacion_servicio', $pqrs->calificacion_servicio) == 4 ? 'selected' : '' }}>4 - Bueno</option>
                        <option value="5" {{ old('calificacion_servicio', $pqrs->calificacion_servicio) == 5 ? 'selected' : '' }}>5 - Excelente</option>
                    </select>
                    @error('calificacion_servicio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div>
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                        Observaciones
                    </label>
                    <textarea name="observaciones" id="observaciones" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones', $pqrs->observaciones) }}</textarea>
                    @error('observaciones')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($pqrs->fecha_respuesta)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Respuesta</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                            {{ $pqrs->fecha_respuesta->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                @endif

                @if($pqrs->respondidoPor)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Respondido Por</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                            {{ $pqrs->respondidoPor->nombre }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('admin.pqrs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
