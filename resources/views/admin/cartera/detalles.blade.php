@extends('admin.layouts.app')

@section('title', 'Detalles de Cartera - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalles de Movimientos de Cartera</h1>
            <p class="mt-2 text-sm text-gray-600">
                @if($cartera->unidad)
                    Unidad: {{ $cartera->unidad->numero }}
                    @if($cartera->unidad->torre)
                        - Torre {{ $cartera->unidad->torre }}
                    @endif
                    @if($cartera->unidad->bloque)
                        - Bloque {{ $cartera->unidad->bloque }}
                    @endif
                @else
                    Unidad no encontrada
                @endif
            </p>
        </div>
        <a href="{{ route('admin.cartera.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Información de la Cartera -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de la Cartera</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">Saldo Corriente</label>
                <p class="mt-1 text-lg font-semibold {{ $cartera->saldo_corriente > 0 ? 'text-green-600' : 'text-gray-900' }}">
                    ${{ number_format($cartera->saldo_corriente, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Saldo en Mora</label>
                <p class="mt-1 text-lg font-semibold {{ $cartera->saldo_mora > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    ${{ number_format($cartera->saldo_mora, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Saldo Total</label>
                <p class="mt-1 text-lg font-semibold {{ $cartera->saldo_total > 0 ? 'text-red-600' : ($cartera->saldo_total < 0 ? 'text-green-600' : 'text-gray-900') }}">
                    ${{ number_format($cartera->saldo_total, 2, ',', '.') }}
                </p>
            </div>
        </div>
        @if($cartera->ultima_actualizacion)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-500">Última Actualización</label>
                <p class="mt-1 text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($cartera->ultima_actualizacion)->format('d/m/Y H:i') }}
                </p>
            </div>
        @endif
    </div>

    <!-- Tabla de Detalles -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Movimientos Registrados</h2>
            <p class="mt-1 text-sm text-gray-600">Historial de movimientos de cuenta de cobro ordenados por fecha</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">FECHA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">PERÍODO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CONCEPTO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO CUENTA</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">VALOR</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($detalles as $detalle)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($detalle->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($detalle->cuentaCobro)
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $detalle->cuentaCobro->periodo)->format('m/Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $detalle->concepto }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($detalle->cuentaCobro)
                                    @if($detalle->cuentaCobro->estado === 'pagada')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Pagada
                                        </span>
                                    @elseif($detalle->cuentaCobro->estado === 'pendiente')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pendiente
                                        </span>
                                    @elseif($detalle->cuentaCobro->estado === 'vencida')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Vencida
                                        </span>
                                    @elseif($detalle->cuentaCobro->estado === 'anulada')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Anulada
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($detalle->cuentaCobro->estado) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ isset($detalle->valor_neto) && $detalle->valor_neto > 0 ? 'text-red-600' : (isset($detalle->valor_neto) && $detalle->valor_neto < 0 ? 'text-green-600' : 'text-gray-900') }}">
                                ${{ number_format(isset($detalle->valor_neto) ? $detalle->valor_neto : $detalle->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron movimientos registrados para esta cartera.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
