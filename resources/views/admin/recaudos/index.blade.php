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
        <div class="table-container">
            <table class="table-domoph min-w-full">
                <thead>
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
                <tbody>
                    @forelse($recaudos as $recaudo)
                        <tr>
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
                                <button 
                                    onclick="verRecaudo({{ $recaudo->id }})" 
                                    class="text-blue-600 hover:text-blue-900" 
                                    title="Ver detalles"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
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

<!-- Modal para ver detalles del recaudo -->
<div id="recaudoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detalles del Recaudo</h3>
                <button onclick="cerrarModalRecaudo()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="recaudoContent" class="mt-4">
                <!-- Contenido se cargará dinámicamente -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                    <p class="mt-2 text-gray-500">Cargando información...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let recaudosData = {};

    // Cargar datos de todos los recaudos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($recaudos as $recaudo)
            @php
                $recaudoFormateado = [
                    'id' => $recaudo->id,
                    'numero_recaudo' => $recaudo->numero_recaudo,
                    'fecha_pago' => $recaudo->fecha_pago ? \Carbon\Carbon::parse($recaudo->fecha_pago)->format('d/m/Y H:i') : '-',
                    'valor_pagado' => number_format($recaudo->valor_pagado, 2, ',', '.'),
                    'tipo_pago' => ucfirst($recaudo->tipo_pago ?? ''),
                    'medio_pago' => ucfirst($recaudo->medio_pago ?? ''),
                    'referencia_pago' => $recaudo->referencia_pago ?? '',
                    'descripcion' => $recaudo->descripcion ?? '',
                    'estado' => ucfirst($recaudo->estado ?? ''),
                    'unidad' => $recaudo->unidad ? $recaudo->unidad->numero . ($recaudo->unidad->torre ? ' - Torre ' . $recaudo->unidad->torre : '') . ($recaudo->unidad->bloque ? ' - Bloque ' . $recaudo->unidad->bloque : '') : 'N/A',
                    'cuenta_cobro' => $recaudo->cuentaCobro ? (\Carbon\Carbon::createFromFormat('Y-m', $recaudo->cuentaCobro->periodo)->locale('es')->translatedFormat('F Y')) : 'Abono general',
                    'registrado_por' => $recaudo->registradoPor ? $recaudo->registradoPor->nombre : 'N/A',
                    'detalles' => $recaudo->detalles->map(function($detalle) {
                        return [
                            'concepto' => $detalle->concepto ?? '',
                            'valor_aplicado' => number_format($detalle->valor_aplicado ?? 0, 2, ',', '.'),
                        ];
                    })->toArray(),
                ];
            @endphp
            recaudosData[{{ $recaudo->id }}] = @json($recaudoFormateado);
        @endforeach
    });

    function verRecaudo(recaudoId) {
        const modal = document.getElementById('recaudoModal');
        const content = document.getElementById('recaudoContent');
        
        if (!recaudosData[recaudoId]) {
            content.innerHTML = '<div class="text-center py-8 text-gray-500">No se encontró información del recaudo.</div>';
        } else {
            const recaudo = recaudosData[recaudoId];
            
            let html = `
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-lg p-4">
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
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                    recaudo.estado === 'Aplicado' ? 'bg-green-100 text-green-800' : 
                                    recaudo.estado === 'Registrado' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800'
                                }">
                                    ${recaudo.estado}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Unidad</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.unidad}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Cuenta de Cobro</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.cuenta_cobro}</p>
                            </div>
                            ${recaudo.referencia_pago ? `
                                <div>
                                    <p class="text-sm text-gray-500">Referencia de Pago</p>
                                    <p class="text-base font-semibold text-gray-900">${recaudo.referencia_pago}</p>
                                </div>
                            ` : ''}
                            <div>
                                <p class="text-sm text-gray-500">Registrado por</p>
                                <p class="text-base font-semibold text-gray-900">${recaudo.registrado_por}</p>
                            </div>
                            ${recaudo.descripcion ? `
                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500">Descripción</p>
                                    <p class="text-base text-gray-900">${recaudo.descripcion}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
            `;
            
            // Mostrar detalles del recaudo si existen
            if (recaudo.detalles && recaudo.detalles.length > 0) {
                html += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Detalles del Recaudo</h4>
                        <div class="space-y-2">
                `;
                
                recaudo.detalles.forEach(function(detalle) {
                    html += `
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-700">${detalle.concepto}</span>
                            <span class="text-sm font-semibold text-gray-900">$${detalle.valor_aplicado}</span>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }
            
            html += '</div>';
            content.innerHTML = html;
        }
        
        modal.classList.remove('hidden');
    }

    function cerrarModalRecaudo() {
        const modal = document.getElementById('recaudoModal');
        modal.classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('recaudoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalRecaudo();
        }
    });
</script>

@endsection
