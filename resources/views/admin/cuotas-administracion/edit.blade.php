@extends('admin.layouts.app')

@section('title', 'Editar Cuota Administración - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Cuota de Administración</h1>
            <p class="mt-2 text-sm text-gray-600">Modifica la información de la cuota</p>
        </div>
        <a href="{{ route('admin.cuotas-administracion.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.cuotas-administracion.update', $cuotaAdministracion->id) }}" method="POST" id="formCuotaAdministracion">
        @csrf
        @method('PUT')

        <!-- Información Básica -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de la Cuota</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Concepto (Solo lectura) -->
                <div>
                    <label for="concepto" class="block text-sm font-medium text-gray-700 mb-2">
                        Concepto
                    </label>
                    <input 
                        type="text" 
                        id="concepto" 
                        value="{{ $cuotaAdministracion->concepto === 'cuota_ordinaria' ? 'Cuota Ordinaria' : 'Cuota Extraordinaria' }}" 
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600"
                    >
                    @if($cuotaAdministracion->concepto === 'cuota_ordinaria')
                        <p class="mt-1 text-xs text-gray-500">El concepto no se puede modificar para cuotas ordinarias.</p>
                    @endif
                </div>

                <!-- Coeficiente (Solo lectura) -->
                <div>
                    <label for="coeficiente" class="block text-sm font-medium text-gray-700 mb-2">
                        Coeficiente
                    </label>
                    <input 
                        type="text" 
                        id="coeficiente" 
                        value="{{ $cuotaAdministracion->coeficiente ? number_format($cuotaAdministracion->coeficiente, 4) : '-' }}" 
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600"
                    >
                    <p class="mt-1 text-xs text-gray-500">El coeficiente no se puede modificar.</p>
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
                            value="{{ old('valor', $cuotaAdministracion->valor) }}" 
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
                        type="month" 
                        id="mes_desde" 
                        name="mes_desde" 
                        value="{{ old('mes_desde', $cuotaAdministracion->mes_desde ? \Carbon\Carbon::parse($cuotaAdministracion->mes_desde)->format('Y-m') : '') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('mes_desde') border-red-500 @enderror"
                    >
                    <p class="mt-1 text-xs text-gray-500">Seleccione mes y año. Dejar vacío si la cuota es indefinida</p>
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
                        type="month" 
                        id="mes_hasta" 
                        name="mes_hasta" 
                        value="{{ old('mes_hasta', $cuotaAdministracion->mes_hasta ? \Carbon\Carbon::parse($cuotaAdministracion->mes_hasta)->format('Y-m') : '') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('mes_hasta') border-red-500 @enderror"
                    >
                    <p class="mt-1 text-xs text-gray-500">Seleccione mes y año. Dejar vacío si la cuota es indefinida</p>
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
                            {{ old('activo', $cuotaAdministracion->activo) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="activo" class="ml-2 block text-sm text-gray-900">
                            Activo
                        </label>
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

@endsection
