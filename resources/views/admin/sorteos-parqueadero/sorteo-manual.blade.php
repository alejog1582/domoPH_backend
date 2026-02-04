@extends('admin.layouts.app')

@section('title', 'Sorteo Manual - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sorteo Manual</h1>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parqueadero</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($participantesCarro as $index => $participante)
                    <tr id="participante-{{ $participante->id }}" class="{{ $participante->parqueadero_asignado === 'Balota blanca' ? 'bg-red-50' : '' }}">
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
                                    Balota Blanca
                                </span>
                            @elseif($participante->parqueadero_asignado)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $participante->parqueadero_asignado }}
                                </span>
                            @else
                                <select id="parqueadero-{{ $participante->id }}" 
                                    class="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    @foreach($parqueaderosCarro as $parqueadero)
                                        @if(!in_array($parqueadero->codigo, $parqueaderosAsignados) || $parqueadero->codigo === $participante->parqueadero_asignado)
                                            <option value="{{ $parqueadero->codigo }}" 
                                                {{ $participante->parqueadero_asignado === $parqueadero->codigo ? 'selected' : '' }}>
                                                {{ $parqueadero->codigo }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($participante->parqueadero_asignado !== 'Balota blanca')
                                <div class="flex gap-2">
                                    <button onclick="guardarParqueadero({{ $sorteo->id }}, {{ $participante->id }})" 
                                        class="text-blue-600 hover:text-blue-900 {{ $participante->parqueadero_asignado ? 'hidden' : '' }}"
                                        id="btn-guardar-{{ $participante->id }}">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                    <button onclick="asignarBalotaBlanca({{ $sorteo->id }}, {{ $participante->id }})" 
                                        class="text-red-600 hover:text-red-900 {{ $participante->parqueadero_asignado ? 'hidden' : '' }}"
                                        id="btn-balota-{{ $participante->id }}">
                                        <i class="fas fa-times-circle"></i> Balota Blanca
                                    </button>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Bloqueado</span>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parqueadero</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($participantesMoto as $index => $participante)
                    <tr id="participante-{{ $participante->id }}" class="{{ $participante->parqueadero_asignado === 'Balota blanca' ? 'bg-red-50' : '' }}">
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
                                    Balota Blanca
                                </span>
                            @elseif($participante->parqueadero_asignado)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $participante->parqueadero_asignado }}
                                </span>
                            @else
                                <select id="parqueadero-{{ $participante->id }}" 
                                    class="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    @foreach($parqueaderosMoto as $parqueadero)
                                        @if(!in_array($parqueadero->codigo, $parqueaderosAsignados) || $parqueadero->codigo === $participante->parqueadero_asignado)
                                            <option value="{{ $parqueadero->codigo }}" 
                                                {{ $participante->parqueadero_asignado === $parqueadero->codigo ? 'selected' : '' }}>
                                                {{ $parqueadero->codigo }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($participante->parqueadero_asignado !== 'Balota blanca')
                                <div class="flex gap-2">
                                    <button onclick="guardarParqueadero({{ $sorteo->id }}, {{ $participante->id }})" 
                                        class="text-blue-600 hover:text-blue-900 {{ $participante->parqueadero_asignado ? 'hidden' : '' }}"
                                        id="btn-guardar-{{ $participante->id }}">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                    <button onclick="asignarBalotaBlanca({{ $sorteo->id }}, {{ $participante->id }})" 
                                        class="text-red-600 hover:text-red-900 {{ $participante->parqueadero_asignado ? 'hidden' : '' }}"
                                        id="btn-balota-{{ $participante->id }}">
                                        <i class="fas fa-times-circle"></i> Balota Blanca
                                    </button>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Bloqueado</span>
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

<!-- Modal Confirmación Balota Blanca -->
<div id="modalBalotaBlanca" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Confirmar Balota Blanca</h3>
            <p class="text-gray-600 mb-4">¿Está seguro de asignar una balota blanca a este participante? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end gap-2">
                <button onclick="cerrarModalBalotaBlanca()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </button>
                <button id="confirmarBalotaBlanca" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let participanteBalotaBlanca = null;
let sorteoIdBalotaBlanca = null;

function guardarParqueadero(sorteoId, participanteId) {
    const select = document.getElementById(`parqueadero-${participanteId}`);
    const parqueaderoCodigo = select.value;

    if (!parqueaderoCodigo) {
        alert('Por favor seleccione un parqueadero');
        return;
    }

    const formData = new FormData();
    formData.append('participante_id', participanteId);
    formData.append('parqueadero_codigo', parqueaderoCodigo);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`/admin/sorteos-parqueadero/${sorteoId}/asignar-parqueadero`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al asignar parqueadero');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al asignar parqueadero');
    });
}

function asignarBalotaBlanca(sorteoId, participanteId) {
    sorteoIdBalotaBlanca = sorteoId;
    participanteBalotaBlanca = participanteId;
    document.getElementById('modalBalotaBlanca').classList.remove('hidden');
}

function cerrarModalBalotaBlanca() {
    document.getElementById('modalBalotaBlanca').classList.add('hidden');
    participanteBalotaBlanca = null;
    sorteoIdBalotaBlanca = null;
}

document.getElementById('confirmarBalotaBlanca').addEventListener('click', function() {
    if (!participanteBalotaBlanca || !sorteoIdBalotaBlanca) return;

    const formData = new FormData();
    formData.append('participante_id', participanteBalotaBlanca);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`/admin/sorteos-parqueadero/${sorteoIdBalotaBlanca}/asignar-balota-blanca`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al asignar balota blanca');
            cerrarModalBalotaBlanca();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al asignar balota blanca');
        cerrarModalBalotaBlanca();
    });
});
</script>
@endsection
