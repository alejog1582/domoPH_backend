@extends('admin.layouts.app')

@section('title', 'Editar Votación - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Votación</h1>
            <p class="mt-2 text-sm text-gray-600">Edita la información de la votación</p>
        </div>
        <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'votaciones']) }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.encuestas-votaciones.update', $votacion->id) }}" method="POST" id="formEncuestaVotacion">
        @csrf
        @method('PUT')
        <input type="hidden" name="tipo" value="votacion">

        <!-- Título -->
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $votacion->titulo) }}" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror"
                placeholder="Ej: Votación para mejoras en zonas comunes">
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
                placeholder="Describe el propósito de la votación">{{ old('descripcion', $votacion->descripcion) }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Fecha Inicio -->
            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Inicio <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" 
                    value="{{ old('fecha_inicio', $votacion->fecha_inicio->format('Y-m-d')) }}" required
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
                    value="{{ old('fecha_fin', $votacion->fecha_fin->format('Y-m-d')) }}" required
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
                    <option value="activa" {{ old('estado', $votacion->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="cerrada" {{ old('estado', $votacion->estado) == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    <option value="anulada" {{ old('estado', $votacion->estado) == 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Opciones de Votación -->
        <div id="opcionesContainer" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Opciones de Votación <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-3">Mínimo 2 opciones requeridas</p>
            
            <div id="opcionesList">
                @if(old('opciones'))
                    @foreach(old('opciones') as $index => $opcion)
                    <div class="opcion-item mb-2 flex items-center gap-2">
                        <input type="hidden" name="opciones_ids[]" value="{{ old('opciones_ids.' . $index, '') }}">
                        <input type="text" name="opciones[]" value="{{ $opcion }}" required
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Texto de la opción">
                        <button type="button" onclick="eliminarOpcion(this)" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforeach
                @else
                    @foreach($votacion->opciones as $index => $opcion)
                    <div class="opcion-item mb-2 flex items-center gap-2">
                        <input type="hidden" name="opciones_ids[]" value="{{ $opcion->id }}">
                        <input type="text" name="opciones[]" value="{{ $opcion->texto_opcion }}" required
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Texto de la opción">
                        <button type="button" onclick="eliminarOpcion(this)" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforeach
                @endif
            </div>
            
            <button type="button" onclick="agregarOpcion()" class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                <i class="fas fa-plus mr-2"></i>Agregar Opción
            </button>
            
            @error('opciones')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('opciones.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-4 mt-6">
            <a href="{{ route('admin.encuestas-votaciones.index', ['tipo' => 'votaciones']) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Actualizar
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Agregar nueva opción
    function agregarOpcion() {
        const opcionesList = document.getElementById('opcionesList');
        const nuevaOpcion = document.createElement('div');
        nuevaOpcion.className = 'opcion-item mb-2 flex items-center gap-2';
        nuevaOpcion.innerHTML = `
            <input type="hidden" name="opciones_ids[]" value="">
            <input type="text" name="opciones[]" required
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Texto de la opción">
            <button type="button" onclick="eliminarOpcion(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        `;
        opcionesList.appendChild(nuevaOpcion);
    }

    // Eliminar opción
    function eliminarOpcion(button) {
        const opcionesList = document.getElementById('opcionesList');
        const opciones = opcionesList.querySelectorAll('.opcion-item');
        
        if (opciones.length > 2) {
            const opcionItem = button.closest('.opcion-item');
            const opcionIdInput = opcionItem.querySelector('input[name="opciones_ids[]"]');
            
            // Si tiene ID, agregarlo a la lista de opciones a eliminar
            if (opcionIdInput && opcionIdInput.value) {
                // Crear input oculto para marcar la opción como eliminada
                const eliminarInput = document.createElement('input');
                eliminarInput.type = 'hidden';
                eliminarInput.name = 'opciones_eliminar[]';
                eliminarInput.value = opcionIdInput.value;
                document.getElementById('formEncuestaVotacion').appendChild(eliminarInput);
            }
            
            opcionItem.remove();
        } else {
            alert('Debe haber al menos 2 opciones');
        }
    }

    // Validación antes de enviar
    document.getElementById('formEncuestaVotacion').addEventListener('submit', function(e) {
        const opciones = document.querySelectorAll('#opcionesList input[name="opciones[]"]');
        const opcionesValidas = Array.from(opciones).filter(op => op.value.trim() !== '');
        
        if (opcionesValidas.length < 2) {
            e.preventDefault();
            alert('Debe haber al menos 2 opciones válidas');
            return false;
        }
    });
</script>
@endpush
@endsection
