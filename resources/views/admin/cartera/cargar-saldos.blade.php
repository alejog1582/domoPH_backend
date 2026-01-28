@extends('admin.layouts.app')

@section('title', 'Cargar Saldos Iniciales - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cargar Saldos Iniciales de Cartera</h1>
            <p class="mt-2 text-sm text-gray-600">Carga masiva de saldos iniciales para todas las unidades</p>
        </div>
        <a href="{{ route('admin.cartera.index') }}" class="text-gray-600 hover:text-gray-900">
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
                    Información Importante
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p class="mb-2">
                        En esta sección puedes cargar los saldos iniciales de cartera para todas las unidades de la copropiedad.
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>El archivo debe contener <strong>exactamente {{ $totalUnidades }} registro(s)</strong> (uno por cada unidad).</li>
                        <li>Debes descargar la plantilla que incluye todas las unidades con saldos en cero.</li>
                        <li>Edita los saldos en la plantilla según corresponda.</li>
                        <li>Sube el archivo completado para actualizar los saldos de cartera.</li>
                        <li>Se registrará un movimiento en el historial de cuenta de cobro para trazabilidad.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Descargar Plantilla -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Descargar Plantilla</h2>
        <p class="text-sm text-gray-600 mb-4">
            Descarga la plantilla Excel que contiene todas las unidades con saldos en cero. 
            Edita los valores según corresponda y luego súbela para actualizar los saldos.
        </p>
        <a href="{{ route('admin.cartera.download-template') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <i class="fas fa-download mr-2"></i>
            Descargar Plantilla
        </a>
    </div>

    <!-- Cargar Archivo -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Cargar Archivo con Saldos</h2>
        <form action="{{ route('admin.cartera.import-saldos') }}" method="POST" enctype="multipart/form-data">
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
                    El archivo debe ser el mismo que descargaste con los saldos editados. Tamaño máximo: 10MB.
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
                                Esta operación actualizará los saldos de todas las unidades. 
                                Asegúrate de que el archivo contenga exactamente {{ $totalUnidades }} registro(s) 
                                y que los valores sean correctos antes de proceder.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.cartera.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-upload mr-2"></i>
                    Cargar Saldos
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
