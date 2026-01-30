@extends('admin.layouts.app')

@section('title', 'Acuerdos de Pago - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Acuerdos de Pago</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión y consulta de acuerdos de pago</p>
        </div>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.acuerdos-pagos.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Filtro por Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos (excepto cumplido y cancelado)</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="incumplido" {{ request('estado') == 'incumplido' ? 'selected' : '' }}>Incumplido</option>
                        <option value="cumplido" {{ request('estado') == 'cumplido' ? 'selected' : '' }}>Cumplido</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
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

                <!-- Filtro por Número de Acuerdo -->
                <div>
                    <label for="numero_acuerdo" class="block text-sm font-medium text-gray-700 mb-2">Número de Acuerdo</label>
                    <input 
                        type="text" 
                        id="numero_acuerdo" 
                        name="numero_acuerdo" 
                        value="{{ request('numero_acuerdo') }}" 
                        placeholder="Buscar por número"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Filtro por Fecha Acuerdo Desde -->
                <div>
                    <label for="fecha_acuerdo_desde" class="block text-sm font-medium text-gray-700 mb-2">Fecha Acuerdo Desde</label>
                    <input 
                        type="date" 
                        id="fecha_acuerdo_desde" 
                        name="fecha_acuerdo_desde" 
                        value="{{ request('fecha_acuerdo_desde') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filtro por Fecha Acuerdo Hasta -->
                <div>
                    <label for="fecha_acuerdo_hasta" class="block text-sm font-medium text-gray-700 mb-2">Fecha Acuerdo Hasta</label>
                    <input 
                        type="date" 
                        id="fecha_acuerdo_hasta" 
                        name="fecha_acuerdo_hasta" 
                        value="{{ request('fecha_acuerdo_hasta') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.acuerdos-pagos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Acuerdos de Pago -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NÚMERO ACUERDO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">UNIDAD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA ACUERDO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA INICIO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA FIN</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">VALOR ACORDADO</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">SALDO PENDIENTE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CUOTAS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($acuerdos as $acuerdo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $acuerdo->numero_acuerdo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($acuerdo->unidad)
                                    {{ $acuerdo->unidad->numero }}
                                    @if($acuerdo->unidad->torre)
                                        - Torre {{ $acuerdo->unidad->torre }}
                                    @endif
                                    @if($acuerdo->unidad->bloque)
                                        - Bloque {{ $acuerdo->unidad->bloque }}
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($acuerdo->fecha_acuerdo)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($acuerdo->fecha_inicio)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $acuerdo->fecha_fin ? \Carbon\Carbon::parse($acuerdo->fecha_fin)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                ${{ number_format($acuerdo->valor_acordado, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                ${{ number_format($acuerdo->saldo_pendiente, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $acuerdo->numero_cuotas }} cuota(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($acuerdo->estado === 'pendiente')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                @elseif($acuerdo->estado === 'activo')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Activo
                                    </span>
                                @elseif($acuerdo->estado === 'cumplido')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Cumplido
                                    </span>
                                @elseif($acuerdo->estado === 'incumplido')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Incumplido
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Cancelado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.acuerdos-pagos.edit', $acuerdo->id) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron acuerdos de pago.
                                @if(request('estado') || request('unidad_id') || request('buscar_unidad') || request('numero_acuerdo') || request('fecha_acuerdo_desde') || request('fecha_acuerdo_hasta'))
                                    Intenta con otro criterio de búsqueda.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($acuerdos->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $acuerdos->links() }}
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
