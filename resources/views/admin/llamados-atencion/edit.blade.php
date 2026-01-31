@extends('admin.layouts.app')

@section('title', 'Gestionar Llamado de Atención - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestionar Llamado de Atención</h1>
            <p class="mt-2 text-sm text-gray-600">ID: <strong>#{{ $llamado->id }}</strong></p>
        </div>
        <a href="{{ route('admin.llamados-atencion.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Información Principal (Solo Lectura) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información del Llamado</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    @if($llamado->unidad)
                        {{ $llamado->unidad->numero }}
                        @if($llamado->unidad->torre) - Torre {{ $llamado->unidad->torre }} @endif
                        @if($llamado->unidad->bloque) - Bloque {{ $llamado->unidad->bloque }} @endif
                    @else
                        General
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ ucfirst($llamado->tipo) }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $llamado->motivo }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $llamado->descripcion }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        @if($llamado->nivel == 'grave') bg-red-100 text-red-800
                        @elseif($llamado->nivel == 'moderado') bg-yellow-100 text-yellow-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ ucfirst($llamado->nivel) }}
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha del Evento</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $llamado->fecha_evento->format('d/m/Y H:i') }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Registro</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $llamado->fecha_registro->format('d/m/Y H:i') }}
                </div>
            </div>

            @if($llamado->evidencia)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Soporte</label>
                    <div class="px-3 py-2 bg-gray-50 rounded-md">
                        @php
                            $evidencias = is_string($llamado->evidencia) ? json_decode($llamado->evidencia, true) : $llamado->evidencia;
                        @endphp
                        @if(is_array($evidencias))
                            @foreach($evidencias as $evidencia)
                                <a href="{{ $evidencia }}" target="_blank" class="block mb-2">
                                    <img src="{{ $evidencia }}" alt="Soporte" class="max-w-full h-auto rounded-lg border border-gray-300">
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Gestión (Editable) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Gestión</h2>
        <form action="{{ route('admin.llamados-atencion.update', $llamado->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Estado -->
            <div class="mb-4">
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select name="estado" id="estado" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estado') border-red-500 @enderror">
                    <option value="abierto" {{ old('estado', $llamado->estado) == 'abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="en_proceso" {{ old('estado', $llamado->estado) == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="cerrado" {{ old('estado', $llamado->estado) == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    <option value="anulado" {{ old('estado', $llamado->estado) == 'anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Comentario al Residente -->
            <div class="mb-4">
                <label for="comentario" class="block text-sm font-medium text-gray-700 mb-1">
                    Comentario al Residente
                </label>
                <textarea name="comentario" id="comentario" rows="4"
                    placeholder="Escribe un comentario para el residente..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('comentario') border-red-500 @enderror">{{ old('comentario') }}</textarea>
                @error('comentario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Soporte (Imagen) -->
            <div class="mb-4">
                <label for="soporte" class="block text-sm font-medium text-gray-700 mb-1">
                    Soporte (Imagen opcional)
                </label>
                <input type="file" name="soporte" id="soporte" accept="image/*"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('soporte') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPEG, PNG, JPG, GIF. Tamaño máximo: 5MB</p>
                @error('soporte')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('admin.llamados-atencion.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Historial -->
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Historial</h2>
    <div class="space-y-4">
        @forelse($historial as $registro)
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        @if($registro->estado_anterior && $registro->estado_anterior !== $registro->estado_nuevo)
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Cambio de estado:</span> 
                                {{ ucfirst(str_replace('_', ' ', $registro->estado_anterior)) }} 
                                → 
                                {{ ucfirst(str_replace('_', ' ', $registro->estado_nuevo)) }}
                            </p>
                        @else
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Estado:</span> 
                                {{ ucfirst(str_replace('_', ' ', $registro->estado_nuevo)) }}
                            </p>
                        @endif
                        @if($registro->comentario)
                            <p class="text-sm text-gray-900 mt-1">{{ $registro->comentario }}</p>
                        @endif
                        @if($registro->soporte_url)
                            <div class="mt-2">
                                <img src="{{ $registro->soporte_url }}" alt="Soporte" class="max-w-xs h-auto rounded-lg border border-gray-300">
                            </div>
                        @endif
                        <p class="text-xs text-gray-500 mt-2">
                            {{ \Carbon\Carbon::parse($registro->fecha_cambio)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 text-center py-4">No hay historial registrado</p>
        @endforelse
    </div>
</div>
@endsection
