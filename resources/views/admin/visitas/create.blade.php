@extends('admin.layouts.app')

@section('title', 'Crear Visita - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Visita</h1>
            <p class="mt-2 text-sm text-gray-600">Registrar una nueva visita</p>
        </div>
        <a href="{{ route('admin.visitas.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.visitas.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Unidad -->
            <div>
                <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Unidad <span class="text-red-500">*</span>
                </label>
                <select name="unidad_id" id="unidad_id" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('unidad_id') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
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

            <!-- Residente -->
            <div>
                <label for="residente_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Residente
                </label>
                <select name="residente_id" id="residente_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('residente_id') border-red-500 @enderror">
                    <option value="">Seleccione un residente...</option>
                    @foreach($residentes as $residente)
                        <option value="{{ $residente->id }}" 
                            data-unidad-id="{{ $residente->unidad_id }}"
                            {{ old('residente_id') == $residente->id ? 'selected' : '' }}>
                            {{ $residente->user->nombre ?? 'Sin nombre' }}
                        </option>
                    @endforeach
                </select>
                @error('residente_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Seleccione primero la unidad para filtrar los residentes
                </p>
            </div>

            <!-- Nombre Visitante -->
            <div>
                <label for="nombre_visitante" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del Visitante <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre_visitante" id="nombre_visitante" value="{{ old('nombre_visitante') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre_visitante') border-red-500 @enderror">
                @error('nombre_visitante')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Documento Visitante -->
            <div>
                <label for="documento_visitante" class="block text-sm font-medium text-gray-700 mb-1">
                    Documento del Visitante
                </label>
                <input type="text" name="documento_visitante" id="documento_visitante" value="{{ old('documento_visitante') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('documento_visitante') border-red-500 @enderror">
                @error('documento_visitante')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Visita -->
            <div>
                <label for="tipo_visita" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Visita <span class="text-red-500">*</span>
                </label>
                <select name="tipo_visita" id="tipo_visita" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo_visita') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="peatonal" {{ old('tipo_visita') == 'peatonal' ? 'selected' : '' }}>Peatonal</option>
                    <option value="vehicular" {{ old('tipo_visita') == 'vehicular' ? 'selected' : '' }}>Vehicular</option>
                </select>
                @error('tipo_visita')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Placa Vehículo -->
            <div id="placa_container" style="display: none;">
                <label for="placa_vehiculo" class="block text-sm font-medium text-gray-700 mb-1">
                    Placa del Vehículo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="placa_vehiculo" id="placa_vehiculo" value="{{ old('placa_vehiculo') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('placa_vehiculo') border-red-500 @enderror">
                @error('placa_vehiculo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Parqueadero (solo para visitas vehiculares) -->
            <div id="parqueadero_container" style="display: none;">
                <label for="parqueadero_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Parqueadero <span class="text-red-500">*</span>
                </label>
                <select name="parqueadero_id" id="parqueadero_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('parqueadero_id') border-red-500 @enderror">
                    <option value="">Seleccione un parqueadero...</option>
                    @foreach($parqueaderosVisitantes as $parqueadero)
                        <option value="{{ $parqueadero->id }}" {{ old('parqueadero_id') == $parqueadero->id ? 'selected' : '' }}>
                            {{ $parqueadero->codigo }} 
                            @if($parqueadero->nivel) - {{ $parqueadero->nivel }} @endif
                            @if($parqueadero->tipo_vehiculo) ({{ ucfirst($parqueadero->tipo_vehiculo) }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('parqueadero_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($parqueaderosVisitantes->count() === 0)
                    <p class="mt-1 text-sm text-yellow-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        No hay parqueaderos de visitantes disponibles.
                    </p>
                @endif
            </div>

            <!-- Fecha Ingreso -->
            <div>
                <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y Hora de Ingreso <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="fecha_ingreso" id="fecha_ingreso" value="{{ old('fecha_ingreso', now()->format('Y-m-d\TH:i')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_ingreso') border-red-500 @enderror">
                @error('fecha_ingreso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Motivo -->
            <div>
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Motivo
                </label>
                <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo') border-red-500 @enderror">
                @error('motivo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
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

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.visitas.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Guardar Visita
            </button>
        </div>
    </form>
</div>

<script>
    // Filtrar residentes por unidad seleccionada
    document.getElementById('unidad_id').addEventListener('change', function() {
        const unidadId = this.value;
        const residenteSelect = document.getElementById('residente_id');
        const options = residenteSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                // Mantener visible la opción "Seleccione un residente..."
                option.style.display = 'block';
            } else {
                const unidadIdOption = option.getAttribute('data-unidad-id');
                if (unidadIdOption === unidadId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        });
        
        // Resetear selección de residente si la unidad cambió
        const currentResidenteUnidad = residenteSelect.querySelector('option:checked')?.getAttribute('data-unidad-id');
        if (currentResidenteUnidad !== unidadId) {
            residenteSelect.value = '';
        }
    });

    // Filtrar residentes al cargar la página si hay un valor old
    document.addEventListener('DOMContentLoaded', function() {
        const unidadId = document.getElementById('unidad_id').value;
        if (unidadId) {
            document.getElementById('unidad_id').dispatchEvent(new Event('change'));
        }
    });

    // Manejar cambio de tipo de visita
    document.getElementById('tipo_visita').addEventListener('change', function() {
        const placaContainer = document.getElementById('placa_container');
        const placaInput = document.getElementById('placa_vehiculo');
        const parqueaderoContainer = document.getElementById('parqueadero_container');
        const parqueaderoSelect = document.getElementById('parqueadero_id');
        
        if (this.value === 'vehicular') {
            placaContainer.style.display = 'block';
            placaInput.setAttribute('required', 'required');
            parqueaderoContainer.style.display = 'block';
            parqueaderoSelect.setAttribute('required', 'required');
        } else {
            placaContainer.style.display = 'none';
            placaInput.removeAttribute('required');
            placaInput.value = '';
            parqueaderoContainer.style.display = 'none';
            parqueaderoSelect.removeAttribute('required');
            parqueaderoSelect.value = '';
        }
    });

    // Trigger on page load if old value exists
    if (document.getElementById('tipo_visita').value === 'vehicular') {
        document.getElementById('placa_container').style.display = 'block';
        document.getElementById('parqueadero_container').style.display = 'block';
    }
</script>
@endsection
