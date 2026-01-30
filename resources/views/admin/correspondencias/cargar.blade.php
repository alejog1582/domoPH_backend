@extends('admin.layouts.app')

@section('title', 'Cargar Correspondencia - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cargar Correspondencia</h1>
            <p class="mt-2 text-sm text-gray-600">Instrucciones para cargar correspondencia mediante plantilla</p>
        </div>
        <a href="{{ route('admin.correspondencias.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Instrucciones</h2>
    
    <div class="prose max-w-none">
        <ol class="list-decimal list-inside space-y-3 text-gray-700">
            <li>
                <strong>Descargue la plantilla:</strong> Haga clic en el botón "Descargar Plantilla" para obtener el archivo Excel con el formato correcto.
            </li>
            <li>
                <strong>Complete la información:</strong> Llene la plantilla con los datos de la correspondencia recibida. Los campos requeridos son:
                <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                    <li><strong>numero_unidad:</strong> Número de la unidad (ej: 101, 202)</li>
                    <li><strong>torre_unidad:</strong> Torre de la unidad (opcional)</li>
                    <li><strong>bloque_unidad:</strong> Bloque de la unidad (opcional)</li>
                    <li><strong>tipo:</strong> Tipo de correspondencia (paquete, documento, factura, domicilio, otro)</li>
                    <li><strong>descripcion:</strong> Descripción del contenido</li>
                    <li><strong>remitente:</strong> Nombre del remitente</li>
                    <li><strong>numero_guia:</strong> Número de guía o tracking (opcional)</li>
                    <li><strong>fecha_recepcion:</strong> Fecha de recepción (formato: YYYY-MM-DD HH:MM:SS)</li>
                    <li><strong>estado:</strong> Estado inicial (recibido, entregado, devuelto, perdido)</li>
                </ul>
            </li>
            <li>
                <strong>Guarde el archivo:</strong> Guarde el archivo Excel con extensión .xlsx o .xls
            </li>
            <li>
                <strong>Cargue el archivo:</strong> Use el formulario a continuación para seleccionar y cargar el archivo completado.
            </li>
            <li>
                <strong>Verifique los resultados:</strong> Después de la carga, revise los registros importados en la lista de correspondencia.
            </li>
        </ol>
    </div>

    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Importante</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>La unidad debe existir en el sistema. Use el número, torre y bloque para identificar correctamente la unidad.</li>
                        <li>El formato de fecha debe ser: YYYY-MM-DD HH:MM:SS (ejemplo: 2026-01-29 14:30:00)</li>
                        <li>Los valores de tipo y estado deben coincidir exactamente con las opciones disponibles.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Cargar Archivo</h2>
    
    <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        
        <div>
            <label for="archivo" class="block text-sm font-medium text-gray-700 mb-1">
                Seleccionar Archivo <span class="text-red-500">*</span>
            </label>
            <input type="file" name="archivo" id="archivo" accept=".xlsx,.xls,.csv" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <p class="mt-1 text-xs text-gray-500">Formatos aceptados: .xlsx, .xls, .csv</p>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.correspondencias.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <a href="{{ route('admin.correspondencias.download-template') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <i class="fas fa-download mr-2"></i>Descargar Plantilla
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>Cargar Archivo
            </button>
        </div>
    </form>
</div>
@endsection
