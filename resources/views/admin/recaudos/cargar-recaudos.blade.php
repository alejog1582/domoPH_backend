@extends('admin.layouts.app')

@section('title', 'Cargar Recaudos - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cargar Recaudos</h1>
            <p class="mt-2 text-sm text-gray-600">Carga masiva de recaudos desde archivo Excel</p>
        </div>
        <a href="{{ route('admin.recaudos.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Información -->
    <div class="bg-blue-50 border border-blue-200 rounded-md p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Instrucciones de Carga de Recaudos
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p class="mb-2">
                        En esta sección puedes cargar recaudos de forma masiva desde un archivo Excel. El sistema aplicará automáticamente los pagos a las cuentas de cobro pendientes.
                    </p>
                    <ul class="list-disc list-inside space-y-1 mb-3">
                        <li><strong>Descarga la plantilla</strong> que contiene el formato correcto para los datos.</li>
                        <li><strong>Completa la información</strong> de cada recaudo en la plantilla.</li>
                        <li><strong>Sube el archivo</strong> para procesar los recaudos automáticamente.</li>
                    </ul>
                    <p class="mb-2 font-semibold">Columnas requeridas en la plantilla:</p>
                    <ul class="list-disc list-inside space-y-1 mb-3">
                        <li><strong>numero_unidad:</strong> Número de la unidad (ej: 101, 202)</li>
                        <li><strong>torre_unidad:</strong> Torre de la unidad (opcional, puede estar vacío)</li>
                        <li><strong>bloque_unidad:</strong> Bloque de la unidad (opcional, puede estar vacío)</li>
                        <li><strong>numero_recaudo:</strong> Número único del recaudo (ej: REC-001)</li>
                        <li><strong>fecha_pago:</strong> Fecha y hora del pago (formato: YYYY-MM-DD HH:MM:SS)</li>
                        <li><strong>tipo_pago:</strong> Tipo de pago (parcial, total, anticipo)</li>
                        <li><strong>medio_pago:</strong> Medio de pago (efectivo, transferencia, consignacion, tarjeta, pse, otro)</li>
                        <li><strong>referencia_pago:</strong> Referencia o número de transacción (opcional)</li>
                        <li><strong>descripcion:</strong> Descripción del recaudo (opcional)</li>
                        <li><strong>valor_pagado:</strong> Valor pagado (ej: 500000)</li>
                    </ul>
                    <p class="mb-2 font-semibold">Aplicación automática de pagos:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Los pagos se aplican automáticamente a las cuentas de cobro pendientes.</li>
                        <li>Se aplican de la cuenta más vieja a la más reciente.</li>
                        <li>Si el valor del recaudo es mayor al saldo pendiente, se divide entre múltiples cuentas.</li>
                        <li>Si una cuenta queda en cero, su estado cambia automáticamente a "pagada".</li>
                        <li>Los saldos de cartera se actualizan automáticamente.</li>
                        <li>Si no hay cuentas pendientes, se registra como abono general.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Descargar Plantilla -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Descargar Plantilla</h2>
        <p class="text-sm text-gray-600 mb-4">
            Descarga la plantilla Excel con el formato correcto para cargar recaudos. 
            La plantilla incluye un ejemplo de cómo deben estar los datos.
        </p>
        <a href="{{ route('admin.recaudos.download-template') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <i class="fas fa-download mr-2"></i>
            Descargar Plantilla
        </a>
    </div>

    <!-- Cargar Archivo -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Cargar Archivo con Recaudos</h2>
        <form action="{{ route('admin.recaudos.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label for="archivo" class="block text-sm font-medium text-gray-700 mb-2">
                    Seleccionar archivo Excel (.xlsx, .xls) o CSV
                </label>
                <input 
                    type="file" 
                    id="archivo" 
                    name="archivo" 
                    accept=".xlsx,.xls,.csv"
                    required
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                >
                <p class="mt-2 text-xs text-gray-500">
                    El archivo debe seguir el formato de la plantilla. Tamaño máximo: 10MB.
                </p>
                @error('archivo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Advertencia -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Advertencia
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>
                                Esta operación procesará los recaudos y aplicará los pagos automáticamente. 
                                Asegúrate de que los datos en el archivo sean correctos antes de continuar.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.recaudos.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                    <i class="fas fa-upload mr-2"></i>
                    Cargar Recaudos
                </button>
            </div>
        </form>
    </div>

    @if(session('errores'))
        <!-- Mostrar Errores -->
        <div class="bg-red-50 border border-red-200 rounded-md p-6 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800 mb-2">
                        Errores encontrados al procesar el archivo:
                    </h3>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1 max-h-60 overflow-y-auto">
                        @foreach(session('errores') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@else
    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    No hay propiedad asignada
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Por favor, contacte al administrador para asignar una propiedad.</p>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
