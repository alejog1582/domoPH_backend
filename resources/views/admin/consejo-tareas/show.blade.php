@extends('admin.layouts.app')

@section('title', 'Detalle de Tarea')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-tareas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Tareas
    </a>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Tarea</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $tarea->titulo }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(\App\Helpers\AdminHelper::hasPermission('consejo-tareas.seguimiento'))
            <a href="{{ route('admin.consejo-tareas.gestionar', $tarea->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-tasks mr-2"></i>
                Gestionar
            </a>
            @endif
            @if($puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-tareas.edit'))
            <a href="{{ route('admin.consejo-tareas.edit', $tarea->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            @endif
            @if($puede_eliminar && \App\Helpers\AdminHelper::hasPermission('consejo-tareas.delete'))
            <form action="{{ route('admin.consejo-tareas.destroy', $tarea->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta tarea?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>
                    Eliminar
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

@if(!$puede_editar || !$puede_eliminar)
<div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
    <p class="text-sm text-yellow-800">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Esta tarea está asociada a un acta que ya tiene firmas. No se puede editar ni eliminar.
    </p>
</div>
@endif

<!-- Información de la Tarea -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Tarea</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Título</label>
            <p class="text-lg font-semibold text-gray-900">{{ $tarea->titulo }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
            @php
                $estadoColors = [
                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                    'en_progreso' => 'bg-blue-100 text-blue-800',
                    'bloqueada' => 'bg-red-100 text-red-800',
                    'finalizada' => 'bg-green-100 text-green-800',
                ];
                $estadoColor = $estadoColors[$tarea->estado] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $estadoColor }}">
                {{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}
            </span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Prioridad</label>
            @php
                $prioridadColors = [
                    'baja' => 'bg-gray-100 text-gray-800',
                    'media' => 'bg-yellow-100 text-yellow-800',
                    'alta' => 'bg-red-100 text-red-800',
                ];
                $prioridadColor = $prioridadColors[$tarea->prioridad] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $prioridadColor }}">
                {{ ucfirst($tarea->prioridad) }}
            </span>
        </div>

        @if($tarea->responsable_id)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Responsable</label>
            @php
                $responsable = DB::table('consejo_integrantes')->where('id', $tarea->responsable_id)->first();
            @endphp
            <p class="text-gray-900">{{ $responsable ? $responsable->nombre . ' (' . ucfirst($responsable->cargo) . ')' : 'N/A' }}</p>
        </div>
        @endif

        @if($tarea->fecha_inicio)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Inicio</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($tarea->fecha_inicio)->format('d/m/Y') }}</p>
        </div>
        @endif

        @if($tarea->fecha_vencimiento)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Vencimiento</label>
            <p class="text-gray-900">
                {{ \Carbon\Carbon::parse($tarea->fecha_vencimiento)->format('d/m/Y') }}
                @if(\Carbon\Carbon::parse($tarea->fecha_vencimiento)->isPast() && $tarea->estado != 'finalizada')
                    <span class="ml-2 text-red-600 text-sm">
                        <i class="fas fa-exclamation-triangle"></i> Vencida
                    </span>
                @endif
            </p>
        </div>
        @endif

        @if($tarea->acta_id)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Acta Asociada</label>
            @php
                $acta = DB::table('consejo_actas')->where('id', $tarea->acta_id)->first();
            @endphp
            @if($acta)
            <a href="{{ route('admin.consejo-actas.show', $tarea->acta_id) }}" class="text-blue-600 hover:text-blue-800">
                {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }} - {{ ucfirst($acta->tipo_reunion) }}
            </a>
            @else
            <p class="text-gray-500">Acta no encontrada</p>
            @endif
        </div>
        @endif

        @if($tarea->decision_id)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Decisión Asociada</label>
            @php
                $decision = DB::table('consejo_decisiones')->where('id', $tarea->decision_id)->first();
            @endphp
            @if($decision)
            <a href="{{ route('admin.consejo-decisiones.show', $tarea->decision_id) }}" class="text-blue-600 hover:text-blue-800">
                {{ Str::limit($decision->descripcion, 50) }}
            </a>
            @else
            <p class="text-gray-500">Decisión no encontrada</p>
            @endif
        </div>
        @endif

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Descripción</label>
            <p class="text-gray-900 whitespace-pre-wrap">{{ $tarea->descripcion }}</p>
        </div>
    </div>
</div>

<!-- Seguimientos Recientes -->
@if($seguimientos->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Seguimientos Recientes</h2>
    
    <div class="space-y-4">
        @foreach($seguimientos->take(5) as $seguimiento)
        <div class="border-l-4 border-blue-500 pl-4 py-2">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $seguimiento->comentario }}</p>
                    @if($seguimiento->estado_anterior && $seguimiento->estado_nuevo && $seguimiento->estado_anterior !== $seguimiento->estado_nuevo)
                    <p class="mt-2 text-sm text-gray-600">
                        <i class="fas fa-exchange-alt mr-1"></i>
                        Estado cambiado de <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $seguimiento->estado_anterior)) }}</span> 
                        a <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $seguimiento->estado_nuevo)) }}</span>
                    </p>
                    @endif
                </div>
                <div class="text-right ml-4">
                    <p class="text-sm font-medium text-gray-900">{{ $seguimiento->creador_nombre }}</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($seguimiento->created_at)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($seguimientos->count() > 5)
    <div class="mt-4 text-center">
        <a href="{{ route('admin.consejo-tareas.gestionar', $tarea->id) }}" class="text-blue-600 hover:text-blue-800">
            Ver todos los seguimientos ({{ $seguimientos->count() }})
        </a>
    </div>
    @endif
</div>
@endif

<!-- Archivos Adjuntos -->
@if($archivos->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($archivos as $archivo)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $archivo->nombre_archivo }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $archivo->tipo_archivo ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $archivo->tamaño ? number_format($archivo->tamaño / 1024, 2) . ' KB' : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($archivo->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ $archivo->ruta_archivo }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Ver
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($seguimientos->count() == 0 && $archivos->count() == 0)
<div class="bg-gray-50 rounded-lg shadow p-6 text-center">
    <p class="text-gray-500 mb-4">No hay seguimientos ni archivos adjuntos</p>
    @if(\App\Helpers\AdminHelper::hasPermission('consejo-tareas.seguimiento'))
    <a href="{{ route('admin.consejo-tareas.gestionar', $tarea->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        <i class="fas fa-tasks mr-2"></i>
        Gestionar Tarea
    </a>
    @endif
</div>
@endif
@endsection
