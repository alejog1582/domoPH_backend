@extends('admin.layouts.app')

@section('title', 'Crear Autorización - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Autorización</h1>
            <p class="mt-2 text-sm text-gray-600">Registrar una nueva autorización de acceso</p>
        </div>
        <a href="{{ route('admin.autorizaciones.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.autorizaciones.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Unidad (Opcional) -->
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

            <!-- Nombre Autorizado -->
            <div>
                <label for="nombre_autorizado" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del Autorizado <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre_autorizado" id="nombre_autorizado" value="{{ old('nombre_autorizado') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre_autorizado') border-red-500 @enderror">
                @error('nombre_autorizado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Documento Autorizado -->
            <div>
                <label for="documento_autorizado" class="block text-sm font-medium text-gray-700 mb-1">
                    Documento del Autorizado
                </label>
                <input type="text" name="documento_autorizado" id="documento_autorizado" value="{{ old('documento_autorizado') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('documento_autorizado') border-red-500 @enderror">
                @error('documento_autorizado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Autorizado -->
            <div>
                <label for="tipo_autorizado" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Autorizado <span class="text-red-500">*</span>
                </label>
                <select name="tipo_autorizado" id="tipo_autorizado" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo_autorizado') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="familiar" {{ old('tipo_autorizado') == 'familiar' ? 'selected' : '' }}>Familiar</option>
                    <option value="empleado" {{ old('tipo_autorizado') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                    <option value="aseo" {{ old('tipo_autorizado') == 'aseo' ? 'selected' : '' }}>Aseo</option>
                    <option value="mantenimiento" {{ old('tipo_autorizado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="proveedor" {{ old('tipo_autorizado') == 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                    <option value="otro" {{ old('tipo_autorizado') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo_autorizado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Acceso -->
            <div>
                <label for="tipo_acceso" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Acceso <span class="text-red-500">*</span>
                </label>
                <select name="tipo_acceso" id="tipo_acceso" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo_acceso') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="peatonal" {{ old('tipo_acceso') == 'peatonal' ? 'selected' : '' }}>Peatonal</option>
                    <option value="vehicular" {{ old('tipo_acceso') == 'vehicular' ? 'selected' : '' }}>Vehicular</option>
                    <option value="ambos" {{ old('tipo_acceso') == 'ambos' ? 'selected' : '' }}>Ambos</option>
                </select>
                @error('tipo_acceso')
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

            <!-- Fecha Inicio -->
            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Inicio
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio') border-red-500 @enderror">
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Fin -->
            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Fin
                </label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_fin') border-red-500 @enderror">
                @error('fecha_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hora Desde -->
            <div>
                <label for="hora_desde" class="block text-sm font-medium text-gray-700 mb-1">
                    Hora Desde
                </label>
                <input type="time" name="hora_desde" id="hora_desde" value="{{ old('hora_desde') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('hora_desde') border-red-500 @enderror">
                @error('hora_desde')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hora Hasta -->
            <div>
                <label for="hora_hasta" class="block text-sm font-medium text-gray-700 mb-1">
                    Hora Hasta
                </label>
                <input type="time" name="hora_hasta" id="hora_hasta" value="{{ old('hora_hasta') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('hora_hasta') border-red-500 @enderror">
                @error('hora_hasta')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Días Autorizados -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Días Autorizados
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @php
                    $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                    $diasSeleccionados = old('dias_autorizados', []);
                @endphp
                @foreach($dias as $dia)
                    <label class="flex items-center">
                        <input type="checkbox" name="dias_autorizados[]" value="{{ $dia }}" 
                            {{ in_array($dia, $diasSeleccionados) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">{{ ucfirst($dia) }}</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-gray-500">Deje sin seleccionar para permitir todos los días</p>
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
            <a href="{{ route('admin.autorizaciones.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Guardar Autorización
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('tipo_acceso').addEventListener('change', function() {
        const placaContainer = document.getElementById('placa_container');
        const placaInput = document.getElementById('placa_vehiculo');
        
        if (this.value === 'vehicular' || this.value === 'ambos') {
            placaContainer.style.display = 'block';
            placaInput.setAttribute('required', 'required');
        } else {
            placaContainer.style.display = 'none';
            placaInput.removeAttribute('required');
            placaInput.value = '';
        }
    });

    // Trigger on page load if old value exists
    const tipoAcceso = document.getElementById('tipo_acceso').value;
    if (tipoAcceso === 'vehicular' || tipoAcceso === 'ambos') {
        document.getElementById('placa_container').style.display = 'block';
    }
</script>
@endsection
