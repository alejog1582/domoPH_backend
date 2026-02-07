@extends('admin.layouts.app')

@section('title', 'Detalle de Acta de Reunión')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-actas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Actas
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Acta de Reunión</h1>
    <p class="text-sm text-gray-600 mt-1">Fecha: {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }}</p>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

<!-- Información del Acta -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información del Acta</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Fecha del Acta</label>
            <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Reunión</label>
            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $acta->tipo_reunion == 'ordinaria' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                {{ ucfirst($acta->tipo_reunion) }}
            </span>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Quorum</label>
            @if($acta->quorum)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    <i class="fas fa-check mr-1"></i>Sí hubo quorum
                </span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    <i class="fas fa-times mr-1"></i>No hubo quorum
                </span>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
            @if($acta->estado == 'borrador')
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Borrador</span>
            @elseif($acta->estado == 'finalizada')
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Finalizada</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Firmada</span>
            @endif
        </div>
        @if($reunion)
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Reunión Asociada</label>
            <a href="{{ route('admin.consejo-reuniones.show', $reunion->id) }}" class="text-blue-600 hover:text-blue-800">
                {{ $reunion->titulo }}
            </a>
        </div>
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Visible para Residentes</label>
            @if($acta->visible_residentes)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sí</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
            @endif
        </div>
    </div>
</div>

<!-- Contenido del Acta -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Contenido del Acta</h2>
    <div class="prose max-w-none">
        {!! $acta->contenido !!}
    </div>
</div>

<!-- Firmas -->
@if($firmas->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Firmas del Acta</h2>
        @php
            $esAdministrador = \Illuminate\Support\Facades\DB::table('administradores_propiedad')
                ->where('user_id', auth()->id())
                ->where('propiedad_id', $propiedad->id)
                ->whereNull('deleted_at')
                ->exists();
            
            $integrante = \Illuminate\Support\Facades\DB::table('consejo_integrantes')
                ->where('user_id', auth()->id())
                ->where('copropiedad_id', $propiedad->id)
                ->where('estado', 'activo')
                ->first();
            
            $esPresidente = $integrante && $integrante->es_presidente == true;
        @endphp
        @if($esAdministrador || $esPresidente)
        <form action="{{ route('admin.consejo-actas.eliminar-firmas', $acta->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar todas las firmas del acta? Esto permitirá agregar decisiones y tareas, pero el acta volverá a estado borrador.');">
            @csrf
            @method('POST')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                <i class="fas fa-trash-alt mr-2"></i>
                Eliminar Todas las Firmas
            </button>
        </form>
        @endif
    </div>
    <div class="space-y-3">
        @foreach($firmas as $firma)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
            <div>
                <p class="font-medium text-gray-900">{{ $firma->nombre }}</p>
                <p class="text-sm text-gray-500">{{ ucfirst($firma->cargo) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-signature mr-1"></i>
                    Firma: {{ \Carbon\Carbon::parse($firma->fecha_firma)->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <p class="text-sm text-yellow-800">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Este acta aún no ha sido firmada por ningún integrante.
    </p>
</div>
@endif

<!-- Archivos Adjuntos -->
@if($archivos->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
    <div class="space-y-3">
        @foreach($archivos as $archivo)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
            <div class="flex items-center">
                <i class="fas fa-file-alt text-gray-400 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">{{ $archivo->nombre_archivo }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $archivo->tipo_archivo }}
                        @if($archivo->tamaño)
                            • {{ number_format($archivo->tamaño / 1024, 2) }} KB
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ $archivo->ruta_archivo }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-external-link-alt"></i>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Decisiones -->
@if($decisiones->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Decisiones del Acta</h2>
    <div class="space-y-3">
        @foreach($decisiones as $decision)
        <div class="border-l-4 border-green-500 pl-4 py-2">
            <p class="font-medium text-gray-900">{{ $decision->descripcion }}</p>
            @if($decision->responsable)
            <p class="text-sm text-gray-500 mt-1">Responsable: {{ $decision->responsable }}</p>
            @endif
            @if($decision->fecha_compromiso)
            <p class="text-sm text-gray-500">Fecha compromiso: {{ \Carbon\Carbon::parse($decision->fecha_compromiso)->format('d/m/Y') }}</p>
            @endif
            <p class="text-sm text-gray-500 mt-1">
                Estado: 
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    {{ $decision->estado == 'completada' ? 'bg-green-100 text-green-800' : 
                       ($decision->estado == 'en_progreso' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($decision->estado) }}
                </span>
            </p>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Acciones -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-end space-x-3">
        @if($puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-actas.edit'))
        <a href="{{ route('admin.consejo-actas.edit', $acta->id) }}" class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
            <i class="fas fa-edit mr-2"></i>
            Editar
        </a>
        @endif
        @if($puede_eliminar && \App\Helpers\AdminHelper::hasPermission('consejo-actas.delete'))
        <form action="{{ route('admin.consejo-actas.destroy', $acta->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este acta?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>
                Eliminar
            </button>
        </form>
        @endif
        @if($acta->estado != 'firmada' && \App\Helpers\AdminHelper::hasPermission('consejo-actas.firmar'))
        @php
            $integrante = \Illuminate\Support\Facades\DB::table('consejo_integrantes')
                ->where('user_id', auth()->id())
                ->where('copropiedad_id', $propiedad->id)
                ->where('estado', 'activo')
                ->first();
            $yaFirmo = $firmas->where('integrante_id', $integrante->id ?? null)->count() > 0;
        @endphp
        @if($integrante && !$yaFirmo)
        <form action="{{ route('admin.consejo-actas.firmar', $acta->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de firmar este acta?');">
            @csrf
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <i class="fas fa-signature mr-2"></i>
                Firmar Acta
            </button>
        </form>
        @endif
        @endif
        @if($reunion)
        <a href="{{ route('admin.consejo-reuniones.show', $reunion->id) }}" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
            <i class="fas fa-calendar-alt mr-2"></i>
            Ver Reunión
        </a>
        @endif
    </div>
</div>
@endsection
