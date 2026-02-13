@extends('admin.layouts.app')

@section('title', 'Cuentas de Cobro - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cuentas de Cobro</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión y consulta de cuentas de cobro</p>
        </div>
    </div>
</div>

@if($propiedad)
    <!-- Resumen de Cuentas de Cobro -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Cuentas de Cobro del Mes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-100">
                        <i class="fas fa-file-invoice-dollar text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Cuentas de Cobro del Mes
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($resumenes['total_mes_actual'], 2, ',', '.') }}
                            </div>
                        </dd>
                        <dd class="mt-1 text-xs text-gray-500">
                            {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('F Y') }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Total Cuentas de Cobro Meses Anteriores -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-100">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Cuentas de Cobro Meses Anteriores
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($resumenes['total_meses_anteriores'], 2, ',', '.') }}
                            </div>
                        </dd>
                        <dd class="mt-1 text-xs text-gray-500">
                            Pendientes
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Total Recaudos del Mes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-100">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Recaudos del Mes
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($resumenes['total_recaudos_mes'], 2, ',', '.') }}
                            </div>
                        </dd>
                        <dd class="mt-1 text-xs text-gray-500">
                            {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('F Y') }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.cuentas-cobro.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Filtro por Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                        <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                        <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>

                <!-- Filtro por Período -->
                <div>
                    <label for="periodo" class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <select 
                        id="periodo" 
                        name="periodo" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        @foreach($periodos as $periodo)
                            @php
                                $fecha = \Carbon\Carbon::createFromFormat('Y-m', $periodo);
                                $periodoLabel = $fecha->locale('es')->translatedFormat('F Y');
                            @endphp
                            <option value="{{ $periodo }}" {{ request('periodo') == $periodo ? 'selected' : '' }}>
                                {{ $periodoLabel }}
                            </option>
                        @endforeach
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
                <a href="{{ route('admin.cuentas-cobro.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Cuentas de Cobro -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="table-container">
            <table class="table-domoph min-w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">PERÍODO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">UNIDAD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA EMISIÓN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA VENCIMIENTO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">VALOR TOTAL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">RECAUDOS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">SALDO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentasCobro as $cuenta)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $fecha = \Carbon\Carbon::createFromFormat('Y-m', $cuenta->periodo);
                                    $periodoLabel = $fecha->locale('es')->translatedFormat('F Y');
                                @endphp
                                {{ $periodoLabel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($cuenta->unidad)
                                    {{ $cuenta->unidad->numero }}
                                    @if($cuenta->unidad->torre)
                                        - Torre {{ $cuenta->unidad->torre }}
                                    @endif
                                    @if($cuenta->unidad->bloque)
                                        - Bloque {{ $cuenta->unidad->bloque }}
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($cuenta->fecha_emision)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cuenta->fecha_vencimiento ? \Carbon\Carbon::parse($cuenta->fecha_vencimiento)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ number_format($cuenta->valor_total, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                @php
                                    $totalRecaudos = $cuenta->recaudos->sum('valor_pagado');
                                    $saldo = $cuenta->valor_total - $totalRecaudos;
                                @endphp
                                ${{ number_format($totalRecaudos, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $saldo > 0 ? 'text-red-600' : 'text-green-600' }}">
                                ${{ number_format($saldo, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($cuenta->estado === 'pendiente')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                @elseif($cuenta->estado === 'pagada')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Pagada
                                    </span>
                                @elseif($cuenta->estado === 'vencida')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Vencida
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Anulada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a 
                                    href="{{ route('admin.cuentas-cobro.pdf', $cuenta->id) }}" 
                                    class="text-green-600 hover:text-green-900 mr-2" 
                                    title="Descargar PDF"
                                    target="_blank"
                                >
                                    <i class="fas fa-download"></i>
                                </a>
                                @if($cuenta->recaudos->count() > 0)
                                    <button 
                                        onclick="verRecaudos({{ $cuenta->id }})" 
                                        class="text-blue-600 hover:text-blue-900 mr-2" 
                                        title="Ver recaudos"
                                    >
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                @endif
                                <!-- <a href="#" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a> -->
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron cuentas de cobro.
                                @if(request('estado') || request('periodo') || request('unidad_id') || request('buscar_unidad'))
                                    Intenta con otro criterio de búsqueda.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($cuentasCobro->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $cuentasCobro->links() }}
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

<!-- Modal para ver recaudos -->
<div id="recaudosModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Recaudos de la Cuenta de Cobro</h3>
                <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="recaudosContent" class="mt-4">
                <!-- Contenido se cargará dinámicamente -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                    <p class="mt-2 text-gray-500">Cargando recaudos...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let recaudosPorCuenta = {};

    // Cargar recaudos de todas las cuentas al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($cuentasCobro as $cuenta)
            @if($cuenta->recaudos->count() > 0)
                @php
                    $recaudosFormateados = $cuenta->recaudos->map(function($recaudo) {
                        return [
                            'id' => $recaudo->id,
                            'numero_recaudo' => $recaudo->numero_recaudo,
                            'fecha_pago' => $recaudo->fecha_pago ? \Carbon\Carbon::parse($recaudo->fecha_pago)->format('d/m/Y') : '-',
                            'valor_pagado' => number_format($recaudo->valor_pagado, 2, ',', '.'),
                            'tipo_pago' => ucfirst($recaudo->tipo_pago ?? ''),
                            'medio_pago' => ucfirst($recaudo->medio_pago ?? ''),
                            'referencia_pago' => $recaudo->referencia_pago ?? '',
                            'descripcion' => $recaudo->descripcion ?? '',
                            'estado' => ucfirst($recaudo->estado ?? ''),
                        ];
                    })->values();
                @endphp
                recaudosPorCuenta[{{ $cuenta->id }}] = @json($recaudosFormateados);
            @endif
        @endforeach
    });

    function verRecaudos(cuentaId) {
        const modal = document.getElementById('recaudosModal');
        const content = document.getElementById('recaudosContent');
        
        if (!recaudosPorCuenta[cuentaId] || recaudosPorCuenta[cuentaId].length === 0) {
            content.innerHTML = '<div class="text-center py-8 text-gray-500">No hay recaudos asociados a esta cuenta de cobro.</div>';
        } else {
            let html = '<div class="space-y-4">';
            
            recaudosPorCuenta[cuentaId].forEach(function(recaudo) {
                html += `
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Número de Recaudo</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.numero_recaudo}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Fecha de Pago</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.fecha_pago}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Valor Pagado</p>
                                <p class="text-base font-semibold text-green-600">$${recaudo.valor_pagado}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tipo de Pago</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.tipo_pago}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Medio de Pago</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.medio_pago}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Estado</p>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    ${recaudo.estado}
                                </span>
                            </div>
                            ${recaudo.referencia_pago ? `
                                <div>
                                    <p class="text-sm text-gray-500">Referencia de Pago</p>
                                    <p class="text-base font-semibold text-gray-900">${recaudo.referencia_pago}</p>
                                </div>
                            ` : ''}
                            ${recaudo.descripcion ? `
                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500">Descripción</p>
                                    <p class="text-base text-gray-900">${recaudo.descripcion}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            content.innerHTML = html;
        }
        
        modal.classList.remove('hidden');
    }

    function cerrarModal() {
        const modal = document.getElementById('recaudosModal');
        modal.classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('recaudosModal').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });
</script>

@endsection
