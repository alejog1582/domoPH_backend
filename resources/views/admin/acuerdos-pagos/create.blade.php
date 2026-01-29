@extends('admin.layouts.app')

@section('title', 'Crear Acuerdo de Pago - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Acuerdo de Pago</h1>
            <p class="mt-2 text-sm text-gray-600">Registrar un nuevo acuerdo de pago para la unidad</p>
        </div>
        <a href="{{ route('admin.cartera.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

@if($propiedad && $cartera)
    <!-- Información de la Unidad -->
    <div class="bg-blue-50 border border-blue-200 rounded-md p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Información de la Unidad
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p><strong>Unidad:</strong> 
                        @if($cartera->unidad)
                            {{ $cartera->unidad->numero }}
                            @if($cartera->unidad->torre)
                                - Torre {{ $cartera->unidad->torre }}
                            @endif
                            @if($cartera->unidad->bloque)
                                - Bloque {{ $cartera->unidad->bloque }}
                            @endif
                        @else
                            N/A
                        @endif
                    </p>
                    <p class="mt-1"><strong>Saldo en Mora:</strong> 
                        <span class="font-bold text-red-600">${{ number_format($cartera->saldo_mora, 2, ',', '.') }}</span>
                    </p>
                    <p class="mt-1"><strong>Saldo Total:</strong> 
                        ${{ number_format($cartera->saldo_total, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.acuerdos-pagos.store') }}" method="POST" id="formAcuerdoPago">
            @csrf
            
            <input type="hidden" name="cartera_id" value="{{ $cartera->id }}">
            <input type="hidden" name="saldo_original" value="{{ $cartera->saldo_mora }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Columna Izquierda -->
                <div class="space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Información del Acuerdo</h2>

                    <!-- Número de Acuerdo -->
                    <div>
                        <label for="numero_acuerdo" class="block text-sm font-medium text-gray-700 mb-2">
                            Número de Acuerdo <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="numero_acuerdo" 
                            name="numero_acuerdo" 
                            value="{{ old('numero_acuerdo', $numeroAcuerdo) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('numero_acuerdo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha del Acuerdo -->
                    <div>
                        <label for="fecha_acuerdo" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha del Acuerdo <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="fecha_acuerdo" 
                            name="fecha_acuerdo" 
                            value="{{ old('fecha_acuerdo', date('Y-m-d')) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('fecha_acuerdo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha de Inicio -->
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Inicio <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="fecha_inicio" 
                            name="fecha_inicio" 
                            value="{{ old('fecha_inicio', date('Y-m-d')) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('fecha_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha de Fin -->
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Fin (Opcional)
                        </label>
                        <input 
                            type="date" 
                            id="fecha_fin" 
                            name="fecha_fin" 
                            value="{{ old('fecha_fin') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('fecha_fin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Valores Financieros</h2>

                    <!-- Saldo Original (Solo lectura) -->
                    <div>
                        <label for="saldo_original_display" class="block text-sm font-medium text-gray-700 mb-2">
                            Saldo Original
                        </label>
                        <input 
                            type="text" 
                            id="saldo_original_display" 
                            value="${{ number_format($cartera->saldo_mora, 2, ',', '.') }}"
                            readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-700"
                        >
                        <p class="mt-1 text-xs text-gray-500">Este valor se toma del saldo en mora de la cartera</p>
                    </div>

                    <!-- Valor Acordado -->
                    <div>
                        <label for="valor_acordado" class="block text-sm font-medium text-gray-700 mb-2">
                            Valor Acordado <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="valor_acordado" 
                            name="valor_acordado" 
                            step="0.01"
                            min="0"
                            value="{{ old('valor_acordado', $cartera->saldo_mora) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('valor_acordado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valor Inicial -->
                    <div>
                        <label for="valor_inicial" class="block text-sm font-medium text-gray-700 mb-2">
                            Valor Inicial (Cuota Inicial)
                        </label>
                        <input 
                            type="number" 
                            id="valor_inicial" 
                            name="valor_inicial" 
                            step="0.01"
                            min="0"
                            value="{{ old('valor_inicial', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('valor_inicial')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Número de Cuotas -->
                    <div>
                        <label for="numero_cuotas" class="block text-sm font-medium text-gray-700 mb-2">
                            Número de Cuotas <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="numero_cuotas" 
                            name="numero_cuotas" 
                            min="1"
                            value="{{ old('numero_cuotas', 1) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('numero_cuotas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valor de Cuota -->
                    <div>
                        <label for="valor_cuota" class="block text-sm font-medium text-gray-700 mb-2">
                            Valor de Cuota <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="valor_cuota" 
                            name="valor_cuota" 
                            step="0.01"
                            min="0"
                            value="{{ old('valor_cuota', $cartera->saldo_mora) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('valor_cuota')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Interés del Acuerdo -->
                    <div>
                        <label for="interes_acuerdo" class="block text-sm font-medium text-gray-700 mb-2">
                            Interés del Acuerdo (%)
                        </label>
                        <input 
                            type="number" 
                            id="interes_acuerdo" 
                            name="interes_acuerdo" 
                            step="0.01"
                            min="0"
                            max="100"
                            value="{{ old('interes_acuerdo', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('interes_acuerdo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valor de Intereses -->
                    <div>
                        <label for="valor_intereses" class="block text-sm font-medium text-gray-700 mb-2">
                            Valor de Intereses
                        </label>
                        <input 
                            type="number" 
                            id="valor_intereses" 
                            name="valor_intereses" 
                            step="0.01"
                            min="0"
                            value="{{ old('valor_intereses', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('valor_intereses')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex items-center justify-end space-x-3 border-t pt-6">
                <a href="{{ route('admin.cartera.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                    <i class="fas fa-save mr-2"></i>
                    Crear Acuerdo de Pago
                </button>
            </div>
        </form>
    </div>
@else
    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    Error
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>No se pudo cargar la información necesaria para crear el acuerdo de pago.</p>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
// Calcular automáticamente el valor de cuota cuando cambien el valor acordado o número de cuotas
document.addEventListener('DOMContentLoaded', function() {
    const valorAcordado = document.getElementById('valor_acordado');
    const numeroCuotas = document.getElementById('numero_cuotas');
    const valorCuota = document.getElementById('valor_cuota');
    const valorInicial = document.getElementById('valor_inicial');

    function calcularValorCuota() {
        const valor = parseFloat(valorAcordado.value) || 0;
        const inicial = parseFloat(valorInicial.value) || 0;
        const cuotas = parseInt(numeroCuotas.value) || 1;
        
        if (cuotas > 0) {
            const valorRestante = valor - inicial;
            valorCuota.value = (valorRestante / cuotas).toFixed(2);
        }
    }

    valorAcordado.addEventListener('input', calcularValorCuota);
    numeroCuotas.addEventListener('input', calcularValorCuota);
    valorInicial.addEventListener('input', calcularValorCuota);
});
</script>

@endsection
