@extends('admin.layouts.app')

@section('title', 'Cartera Unidades - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cartera de Unidades</h1>
            <p class="mt-2 text-sm text-gray-600">Consulta y visualización de saldos de cartera por unidad</p>
        </div>
        <a href="{{ route('admin.cartera.cargar-saldos') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-upload mr-2"></i>
            Cargar Saldos Iniciales
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Dashboard de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Unidades en Mora -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Unidades en Mora
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ number_format($unidadesEnMora, 0) }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Valor Total de Mora -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-100">
                        <i class="fas fa-dollar-sign text-orange-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Valor Total de Mora
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($valorMoraTotal, 2, ',', '.') }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.cartera.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Filtro por Unidad -->
                <div>
                    <label for="buscar_unidad" class="block text-sm font-medium text-gray-700 mb-2">
                        Buscar Unidad
                    </label>
                    <input 
                        type="text" 
                        id="buscar_unidad" 
                        name="buscar_unidad" 
                        value="{{ request('buscar_unidad') }}" 
                        placeholder="Número, torre o bloque"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label for="estado_unidad" class="block text-sm font-medium text-gray-700 mb-2">
                        Estado de Unidad
                    </label>
                    <select 
                        id="estado_unidad" 
                        name="estado_unidad" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        @foreach($estadosUnidades as $estado)
                            <option value="{{ $estado }}" {{ request('estado_unidad') == $estado ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Unidades en Mora -->
                <div>
                    <label for="en_mora" class="block text-sm font-medium text-gray-700 mb-2">
                        Unidades en Mora
                    </label>
                    <select 
                        id="en_mora" 
                        name="en_mora" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todas</option>
                        <option value="1" {{ request('en_mora') == '1' ? 'selected' : '' }}>Solo en Mora</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.cartera.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

    <!-- Tabla de Carteras -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">UNIDAD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">TIPO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">SALDO CORRIENTE</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">SALDO MORA</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">SALDO TOTAL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ÚLTIMA ACTUALIZACIÓN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($carteras as $cartera)
                        <tr class="hover:bg-gray-50 {{ $cartera->saldo_mora > 0 ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($cartera->unidad)
                                        {{ $cartera->unidad->numero }}
                                        @if($cartera->unidad->torre)
                                            - Torre {{ $cartera->unidad->torre }}
                                        @endif
                                        @if($cartera->unidad->bloque)
                                            - Bloque {{ $cartera->unidad->bloque }}
                                        @endif
                                    @else
                                        <span class="text-gray-400">Unidad no encontrada</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($cartera->unidad)
                                        {{ ucfirst(str_replace('_', ' ', $cartera->unidad->tipo ?? '-')) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cartera->unidad)
                                    @if($cartera->unidad->estado === 'ocupada')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Ocupada
                                        </span>
                                    @elseif($cartera->unidad->estado === 'desocupada')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Desocupada
                                        </span>
                                    @elseif($cartera->unidad->estado === 'en_construccion')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            En Construcción
                                        </span>
                                    @elseif($cartera->unidad->estado === 'mantenimiento')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Mantenimiento
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst(str_replace('_', ' ', $cartera->unidad->estado)) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium {{ $cartera->saldo_corriente > 0 ? 'text-green-600' : 'text-gray-900' }}">
                                    ${{ number_format($cartera->saldo_corriente, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium {{ $cartera->saldo_mora > 0 ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                    ${{ number_format($cartera->saldo_mora, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold {{ $cartera->saldo_total > 0 ? 'text-red-600' : ($cartera->saldo_total < 0 ? 'text-green-600' : 'text-gray-900') }}">
                                    ${{ number_format($cartera->saldo_total, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($cartera->ultima_actualizacion)
                                    {{ \Carbon\Carbon::parse($cartera->ultima_actualizacion)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">Sin actualizar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.cartera.detalles', $cartera->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron registros de cartera. 
                                @if(request('buscar_unidad') || request('estado_unidad') || request('en_mora'))
                                    Intenta con otro criterio de búsqueda.
                                @else
                                    Las carteras se crean automáticamente al crear o importar unidades.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($carteras->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $carteras->links() }}
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
