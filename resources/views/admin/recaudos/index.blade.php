@extends('admin.layouts.app')

@section('title', 'Recaudos - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Recaudos</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión y consulta de recaudos</p>
        </div>
        <a href="{{ route('admin.recaudos.cargar') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-upload mr-2"></i>
            Cargar Recaudos
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.recaudos.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                <!-- Filtro por Fecha Desde -->
                <div>
                    <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                    <input 
                        type="date" 
                        id="fecha_desde" 
                        name="fecha_desde" 
                        value="{{ request('fecha_desde') ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filtro por Fecha Hasta -->
                <div>
                    <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                    <input 
                        type="date" 
                        id="fecha_hasta" 
                        name="fecha_hasta" 
                        value="{{ request('fecha_hasta') ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="registrado" {{ request('estado') == 'registrado' ? 'selected' : '' }}>Registrado</option>
                        <option value="aplicado" {{ request('estado') == 'aplicado' ? 'selected' : '' }}>Aplicado</option>
                        <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                    </select>
                </div>

                <!-- Filtro por Unidad -->
                <div>
                    <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-2">Unidad</label>
                    <select 
                        id="unidad_id" 
                        name="unidad_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todas</option>
                        @foreach($unidades as $unidad)
                            @php
                                $labelUnidad = $unidad->numero;
                                if ($unidad->torre) $labelUnidad .= ' - Torre ' . $unidad->torre;
                                if ($unidad->bloque) $labelUnidad .= ' - Bloque ' . $unidad->bloque;
                            @endphp
                            <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                {{ $labelUnidad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Medio de Pago -->
                <div>
                    <label for="medio_pago" class="block text-sm font-medium text-gray-700 mb-2">Medio de Pago</label>
                    <select 
                        id="medio_pago" 
                        name="medio_pago" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="efectivo" {{ request('medio_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="transferencia" {{ request('medio_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                        <option value="consignacion" {{ request('medio_pago') == 'consignacion' ? 'selected' : '' }}>Consignación</option>
                        <option value="tarjeta" {{ request('medio_pago') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                        <option value="pse" {{ request('medio_pago') == 'pse' ? 'selected' : '' }}>PSE</option>
                        <option value="otro" {{ request('medio_pago') == 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Filtro por Búsqueda de Unidad -->
                <div>
                    <label for="buscar_unidad" class="block text-sm font-medium text-gray-700 mb-2">Buscar Unidad</label>
                    <input 
                        type="text" 
                        id="buscar_unidad" 
                        name="buscar_unidad" 
                        value="{{ request('buscar_unidad') }}" 
                        placeholder="Número, torre o bloque"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.recaudos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times mr-1"></i> Limpiar filtros
                </a>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Recaudos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NÚMERO RECAUDO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">UNIDAD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA PAGO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">VALOR PAGADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">MEDIO PAGO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CUENTA COBRO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recaudos as $recaudo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $recaudo->numero_recaudo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($recaudo->unidad)
                                    {{ $recaudo->unidad->numero }}
                                    @if($recaudo->unidad->torre)
                                        - Torre {{ $recaudo->unidad->torre }}
                                    @endif
                                    @if($recaudo->unidad->bloque)
                                        - Bloque {{ $recaudo->unidad->bloque }}
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($recaudo->fecha_pago)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ number_format($recaudo->valor_pagado, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($recaudo->medio_pago) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($recaudo->cuentaCobro)
                                    @php
                                        $fecha = \Carbon\Carbon::createFromFormat('Y-m', $recaudo->cuentaCobro->periodo);
                                        $periodoLabel = $fecha->locale('es')->translatedFormat('F Y');
                                    @endphp
                                    {{ $periodoLabel }}
                                @else
                                    <span class="text-gray-400">Abono general</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($recaudo->estado === 'registrado')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Registrado
                                    </span>
                                @elseif($recaudo->estado === 'aplicado')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Aplicado
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Anulado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron recaudos.
                                @if(request('fecha_desde') || request('fecha_hasta') || request('estado') || request('unidad_id') || request('buscar_unidad') || request('medio_pago'))
                                    Intenta con otro criterio de búsqueda.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($recaudos->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $recaudos->links() }}
            </div>
        @endif
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
