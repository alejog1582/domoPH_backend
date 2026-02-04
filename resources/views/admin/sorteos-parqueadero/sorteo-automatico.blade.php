@extends('admin.layouts.app')

@section('title', 'Sorteo Automático - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sorteo Automático</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $sorteo->titulo }}</p>
        </div>
        <a href="{{ route('admin.sorteos-parqueadero.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<!-- Participantes Carro -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-car mr-2 text-blue-600"></i>Participantes - Carros
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inscripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($participantesCarro as $index => $participante)
                    <tr id="participante-{{ $participante->id }}" class="{{ $participante->parqueadero_asignado === 'Balota blanca' ? 'bg-red-50' : ($participante->parqueadero_asignado ? 'bg-green-50' : '') }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participante->residente->user->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participante->unidad->numero ?? 'N/A' }} 
                            {{ $participante->unidad->torre ?? '' }} 
                            {{ $participante->unidad->bloque ?? '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $participante->placa }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($participante->fecha_inscripcion)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($participante->parqueadero_asignado === 'Balota blanca')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Balota Blanca
                                </span>
                            @elseif($participante->parqueadero_asignado)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>{{ $participante->parqueadero_asignado }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if(!$participante->parqueadero_asignado)
                                <button onclick="ejecutarSorteo({{ $sorteo->id }}, {{ $participante->id }})" 
                                    id="btn-sortear-{{ $participante->id }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-dice mr-1"></i>Sortear
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">Completado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No hay participantes inscritos para carros
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Participantes Moto -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-motorcycle mr-2 text-green-600"></i>Participantes - Motos
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inscripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($participantesMoto as $index => $participante)
                    <tr id="participante-{{ $participante->id }}" class="{{ $participante->parqueadero_asignado === 'Balota blanca' ? 'bg-red-50' : ($participante->parqueadero_asignado ? 'bg-green-50' : '') }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participante->residente->user->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participante->unidad->numero ?? 'N/A' }} 
                            {{ $participante->unidad->torre ?? '' }} 
                            {{ $participante->unidad->bloque ?? '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $participante->placa }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($participante->fecha_inscripcion)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($participante->parqueadero_asignado === 'Balota blanca')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Balota Blanca
                                </span>
                            @elseif($participante->parqueadero_asignado)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>{{ $participante->parqueadero_asignado }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if(!$participante->parqueadero_asignado)
                                <button onclick="ejecutarSorteo({{ $sorteo->id }}, {{ $participante->id }})" 
                                    id="btn-sortear-{{ $participante->id }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-dice mr-1"></i>Sortear
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">Completado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No hay participantes inscritos para motos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function ejecutarSorteo(sorteoId, participanteId) {
    const btn = document.getElementById(`btn-sortear-${participanteId}`);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Sortando...';

    const formData = new FormData();
    formData.append('participante_id', participanteId);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`/admin/sorteos-parqueadero/${sorteoId}/ejecutar-sorteo-automatico`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar la fila con el resultado
            const row = document.getElementById(`participante-${participanteId}`);
            const resultadoCell = row.cells[5];
            const accionCell = row.cells[6];

            if (data.resultado.tipo === 'parqueadero') {
                resultadoCell.innerHTML = `
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>${data.resultado.codigo}
                    </span>
                `;
                row.classList.remove('bg-red-50');
                row.classList.add('bg-green-50');
            } else {
                resultadoCell.innerHTML = `
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i>Balota Blanca
                    </span>
                `;
                row.classList.remove('bg-green-50');
                row.classList.add('bg-red-50');
            }

            accionCell.innerHTML = '<span class="text-gray-400 text-xs">Completado</span>';
        } else {
            alert(data.error || 'Error al ejecutar el sorteo');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-dice mr-1"></i>Sortear';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al ejecutar el sorteo');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-dice mr-1"></i>Sortear';
    });
}
</script>
@endsection
