@extends('admin.layouts.app')

@section('title', 'Detalle de Reunión del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-reuniones.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Reuniones
    </a>
    <h1 class="text-3xl font-bold text-gray-900">{{ $reunion->titulo }}</h1>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Reunión</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Reunión</label>
            <p class="text-lg font-semibold text-gray-900">{{ ucfirst($reunion->tipo_reunion) }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Modalidad</label>
            <p class="text-lg text-gray-900">{{ ucfirst($reunion->modalidad) }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha y Hora de Inicio</label>
            <p class="text-lg text-gray-900">{{ \Carbon\Carbon::parse($reunion->fecha_inicio)->format('d/m/Y H:i') }}</p>
        </div>
        @if($reunion->fecha_fin)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha y Hora de Fin</label>
            <p class="text-lg text-gray-900">{{ \Carbon\Carbon::parse($reunion->fecha_fin)->format('d/m/Y H:i') }}</p>
        </div>
        @endif
        @if($reunion->lugar)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Lugar</label>
            <p class="text-lg text-gray-900">{{ $reunion->lugar }}</p>
        </div>
        @endif
        @if($reunion->enlace_virtual)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Enlace Virtual</label>
            <a href="{{ $reunion->enlace_virtual }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                {{ $reunion->enlace_virtual }}
            </a>
        </div>
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
            @if($reunion->estado == 'programada')
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Programada</span>
            @elseif($reunion->estado == 'realizada')
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Realizada</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelada</span>
            @endif
        </div>
    </div>

    @if($reunion->observaciones)
    <div class="mt-6">
        <label class="block text-sm font-medium text-gray-500 mb-1">Observaciones</label>
        <p class="text-gray-900 whitespace-pre-wrap">{{ $reunion->observaciones }}</p>
    </div>
    @endif
</div>

<!-- Agenda -->
@if($agenda->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Agenda</h2>
    <div class="space-y-3">
        @foreach($agenda as $punto)
        <div class="border-l-4 border-blue-500 pl-4 py-2">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="font-medium text-gray-900">{{ $punto->tema }}</p>
                    @if($punto->responsable)
                    <p class="text-sm text-gray-500 mt-1">Responsable: {{ $punto->responsable }}</p>
                    @endif
                </div>
                <span class="text-sm text-gray-400">#{{ $punto->orden }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Asistencias -->
@if($asistencias->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Asistencias</h2>
    <div class="space-y-2">
        @foreach($asistencias as $asistencia)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
            <div>
                <p class="font-medium text-gray-900">{{ $asistencia->nombre }}</p>
                <p class="text-sm text-gray-500">{{ ucfirst($asistencia->cargo) }}</p>
            </div>
            <div class="text-right">
                @if($asistencia->asistio)
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i>Asistió
                    </span>
                    @if($asistencia->hora_ingreso)
                    <p class="text-xs text-gray-500 mt-1">Ingreso: {{ \Carbon\Carbon::parse($asistencia->hora_ingreso)->format('H:i') }}</p>
                    @endif
                @else
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times mr-1"></i>No asistió
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Acciones -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-end space-x-3">
        @if($reunion->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('consejo-reuniones.edit'))
        <a href="{{ route('admin.consejo-reuniones.edit', $reunion->id) }}" class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
            <i class="fas fa-edit mr-2"></i>
            Editar
        </a>
        @endif
        @if($reunion->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('consejo-actas.create'))
        <a href="{{ route('admin.consejo-actas.create', ['reunion_id' => $reunion->id]) }}" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            <i class="fas fa-file-signature mr-2"></i>
            Crear Acta
        </a>
        @endif
        @if($reunion->estado == 'realizada' && $acta && \App\Helpers\AdminHelper::hasPermission('consejo-actas.view'))
        <a href="{{ route('admin.consejo-actas.show', $acta->id) }}" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            <i class="fas fa-file-alt mr-2"></i>
            Ver Acta
        </a>
        @endif
    </div>
</div>
@endsection
