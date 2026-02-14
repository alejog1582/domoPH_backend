@extends('admin.layouts.app')

@section('title', 'Reporte Parqueaderos Visitantes - Administrador')

@push('styles')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reporte Parqueaderos Visitantes</h1>
            <p class="mt-2 text-sm text-gray-600">Análisis de usabilidad y recaudo de parqueaderos de visitantes</p>
        </div>
    </div>
</div>

@if($propiedad)
    <!-- Filtros de Fecha -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.reportes.parqueaderos-visitantes') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Inicio
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Fin
                </label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Estadísticas Generales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <i class="fas fa-car text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Visitas</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalVisitas }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <i class="fas fa-check-circle text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Visitas Finalizadas</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $visitasFinalizadas }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <i class="fas fa-clock text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Visitas Activas</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $visitasActivas }}</p>
                </div>
            </div>
        </div>

        @if($cobroActivo)
        <div class="bg-white rounded-lg shadow p-6 md:col-span-3">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-600 rounded-md p-3">
                    <i class="fas fa-money-bill-wave text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Recaudo Total</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        ${{ number_format($recaudoTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Visitas por Día -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Visitas por Día</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="chartVisitasPorDia"></canvas>
            </div>
        </div>

        @if($cobroActivo)
        <!-- Gráfico de Recaudo por Día -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recaudo por Día</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="chartRecaudoPorDia"></canvas>
            </div>
        </div>
        @endif
    </div>

    <!-- Tabla de Estadísticas por Parqueadero -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Estadísticas por Parqueadero</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Parqueadero
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Visitas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Finalizadas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Activas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Horas Ocupación
                        </th>
                        @if($cobroActivo)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Recaudo
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($estadisticasPorParqueadero as $estadistica)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $estadistica['parqueadero']->codigo }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $estadistica['parqueadero']->tipo_vehiculo ? ucfirst($estadistica['parqueadero']->tipo_vehiculo) : 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $estadistica['total_visitas'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $estadistica['visitas_finalizadas'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $estadistica['visitas_activas'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($estadistica['horas_ocupacion'], 1) }} h
                        </td>
                        @if($cobroActivo)
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            ${{ number_format($recaudoPorParqueadero[$estadistica['parqueadero']->id]['recaudo'] ?? 0, 2, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@else
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center py-12">
            <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
            <p class="text-gray-600">No hay propiedad asignada.</p>
        </div>
    </div>
@endif

@push('scripts')
<script>
    // Datos para gráficos
    const estadisticasPorDia = @json($estadisticasPorDia);
    const recaudoPorDia = @json($recaudoPorDia);
    const cobroActivo = @json($cobroActivo);

    // Gráfico de Visitas por Día
    const ctxVisitas = document.getElementById('chartVisitasPorDia');
    if (ctxVisitas) {
        new Chart(ctxVisitas, {
            type: 'line',
            data: {
                labels: estadisticasPorDia.map(item => item.fecha),
                datasets: [
                    {
                        label: 'Total Visitas',
                        data: estadisticasPorDia.map(item => item.total_visitas),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Visitas Finalizadas',
                        data: estadisticasPorDia.map(item => item.visitas_finalizadas),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Recaudo por Día
    if (cobroActivo) {
        const ctxRecaudo = document.getElementById('chartRecaudoPorDia');
        if (ctxRecaudo) {
            new Chart(ctxRecaudo, {
                type: 'bar',
                data: {
                    labels: recaudoPorDia.map(item => item.fecha),
                    datasets: [{
                        label: 'Recaudo ($)',
                        data: recaudoPorDia.map(item => item.recaudo),
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString('es-CO');
                                }
                            }
                        }
                    }
                }
            });
        }
    }
</script>
@endpush

@endsection
