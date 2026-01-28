@extends('admin.layouts.app')

@section('title', 'Crear Cuota Administración - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Rubro de Cobro</h1>
            <p class="mt-2 text-sm text-gray-600">Configura una nueva cuota de administración</p>
        </div>
        <a href="{{ route('admin.cuotas-administracion.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.cuotas-administracion.store') }}" method="POST" id="formCuotaAdministracion">
        @csrf

        <!-- Información Básica -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de la Cuota</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Concepto -->
                <div>
                    <label for="concepto" class="block text-sm font-medium text-gray-700 mb-2">
                        Concepto <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="concepto" 
                        name="concepto" 
                        required
                        onchange="toggleCoeficiente()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('concepto') border-red-500 @enderror"
                    >
                        <option value="">Seleccione un concepto</option>
                        <option value="cuota_ordinaria" {{ old('concepto') == 'cuota_ordinaria' ? 'selected' : '' }}>Cuota Ordinaria</option>
                        <option value="cuota_extraordinaria" {{ old('concepto') == 'cuota_extraordinaria' ? 'selected' : '' }}>Cuota Extraordinaria</option>
                    </select>
                    @error('concepto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coeficiente -->
                <div id="coeficiente-container">
                    <label for="coeficiente" class="block text-sm font-medium text-gray-700 mb-2">
                        Coeficiente <span id="coeficiente-required" class="text-red-500 hidden">*</span>
                    </label>
                    <select 
                        id="coeficiente" 
                        name="coeficiente" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('coeficiente') border-red-500 @enderror"
                    >
                        <option value="">Seleccione un coeficiente</option>
                        @foreach($coeficientes as $coef)
                            <option value="{{ $coef }}" {{ old('coeficiente') == $coef ? 'selected' : '' }}>
                                {{ number_format($coef, 4) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500" id="coeficiente-help">
                        Si la cuota extraordinaria es igual para todos los coeficientes, no es necesario seleccionar un coeficiente.
                    </p>
                    @error('coeficiente')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor -->
                <div>
                    <label for="valor" class="block text-sm font-medium text-gray-700 mb-2">
                        Valor <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input 
                            type="number" 
                            id="valor" 
                            name="valor" 
                            value="{{ old('valor') }}" 
                            required
                            step="0.01"
                            min="0"
                            class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('valor') border-red-500 @enderror"
                            placeholder="0.00"
                        >
                    </div>
                    @error('valor')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mes Desde -->
                <div>
                    <label for="mes_desde" class="block text-sm font-medium text-gray-700 mb-2">
                        Mes Desde
                    </label>
                    <input 
                        type="date" 
                        id="mes_desde" 
                        name="mes_desde" 
                        value="{{ old('mes_desde') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('mes_desde') border-red-500 @enderror"
                    >
                    <p class="mt-1 text-xs text-gray-500">Dejar vacío si la cuota es indefinida</p>
                    @error('mes_desde')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mes Hasta -->
                <div>
                    <label for="mes_hasta" class="block text-sm font-medium text-gray-700 mb-2">
                        Mes Hasta
                    </label>
                    <input 
                        type="date" 
                        id="mes_hasta" 
                        name="mes_hasta" 
                        value="{{ old('mes_hasta') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('mes_hasta') border-red-500 @enderror"
                    >
                    <p class="mt-1 text-xs text-gray-500">Dejar vacío si la cuota es indefinida</p>
                    @error('mes_hasta')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Activo -->
                <div class="md:col-span-2">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="activo" 
                            name="activo" 
                            value="1"
                            {{ old('activo', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="activo" class="ml-2 block text-sm text-gray-900">
                            Activo
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nota Informativa -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Información Importante
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Para <strong>cuotas ordinarias</strong>: El coeficiente es obligatorio y se debe establecer el valor de la cuota ordinaria para cada coeficiente.</li>
                            <li>Para <strong>cuotas extraordinarias</strong>: Si la cuota extraordinaria es igual para todos los coeficientes, no es necesario usar el campo coeficiente. Si cada coeficiente va a usar un valor de cuota extraordinaria diferente, se debe crear cada cuota extraordinaria para cada coeficiente.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.cuotas-administracion.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Guardar
            </button>
        </div>
    </form>
</div>

<script>
function toggleCoeficiente() {
    const concepto = document.getElementById('concepto').value;
    const coeficienteSelect = document.getElementById('coeficiente');
    const coeficienteRequired = document.getElementById('coeficiente-required');
    const coeficienteHelp = document.getElementById('coeficiente-help');
    
    if (concepto === 'cuota_ordinaria') {
        coeficienteSelect.required = true;
        coeficienteRequired.classList.remove('hidden');
        coeficienteHelp.textContent = 'El coeficiente es obligatorio para cuotas ordinarias.';
        coeficienteHelp.classList.remove('text-gray-500');
        coeficienteHelp.classList.add('text-red-600');
    } else if (concepto === 'cuota_extraordinaria') {
        coeficienteSelect.required = false;
        coeficienteRequired.classList.add('hidden');
        coeficienteHelp.textContent = 'Si la cuota extraordinaria es igual para todos los coeficientes, no es necesario seleccionar un coeficiente.';
        coeficienteHelp.classList.remove('text-red-600');
        coeficienteHelp.classList.add('text-gray-500');
    } else {
        coeficienteSelect.required = false;
        coeficienteRequired.classList.add('hidden');
        coeficienteHelp.textContent = 'Si la cuota extraordinaria es igual para todos los coeficientes, no es necesario seleccionar un coeficiente.';
        coeficienteHelp.classList.remove('text-red-600');
        coeficienteHelp.classList.add('text-gray-500');
    }
}

// Ejecutar al cargar la página si hay un concepto seleccionado
document.addEventListener('DOMContentLoaded', function() {
    toggleCoeficiente();
});
</script>

@endsection
