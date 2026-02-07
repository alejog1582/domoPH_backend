@extends('admin.layouts.app')

@section('title', 'Gestionar Tarea')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-tareas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Tareas
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Gestionar Tarea</h1>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<!-- Información de la Tarea -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Tarea</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
            <p class="text-gray-900 font-semibold">{{ $tarea->titulo }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
            @php
                $responsable = DB::table('consejo_integrantes')->where('id', $tarea->responsable_id)->first();
            @endphp
            <p class="text-gray-900">{{ $responsable ? $responsable->nombre : 'N/A' }}</p>
        </div>
        @endif

        @if($tarea->fecha_inicio)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($tarea->fecha_inicio)->format('d/m/Y') }}</p>
        </div>
        @endif

        @if($tarea->fecha_vencimiento)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
            <p class="text-gray-900">{{ \Carbon\Carbon::parse($tarea->fecha_vencimiento)->format('d/m/Y') }}</p>
        </div>
        @endif

        @if($tarea->acta_id)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Acta Asociada</label>
            <a href="{{ route('admin.consejo-actas.show', $tarea->acta_id) }}" class="text-blue-600 hover:text-blue-800">
                Ver Acta
            </a>
        </div>
        @endif

        @if($tarea->decision_id)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Decisión Asociada</label>
            <a href="{{ route('admin.consejo-decisiones.show', $tarea->decision_id) }}" class="text-blue-600 hover:text-blue-800">
                Ver Decisión
            </a>
        </div>
        @endif

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <p class="text-gray-900 whitespace-pre-wrap">{{ $tarea->descripcion }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Agregar Seguimiento -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Agregar Seguimiento</h2>
        
        @php
            $integrante = DB::table('consejo_integrantes')
                ->where('user_id', auth()->id())
                ->where('copropiedad_id', $propiedad->id)
                ->where('estado', 'activo')
                ->first();
            $esPresidente = $integrante && $integrante->es_presidente == true;
        @endphp

        <form action="{{ route('admin.consejo-tareas.seguimiento', $tarea->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="comentario" class="block text-sm font-medium text-gray-700 mb-1">
                    Comentario <span class="text-red-500">*</span>
                </label>
                <textarea name="comentario" id="comentario" rows="4" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('comentario') border-red-500 @enderror"
                    placeholder="Agregue un comentario sobre el avance de la tarea...">{{ old('comentario') }}</textarea>
                @error('comentario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if($esPresidente)
            <div>
                <label for="estado_nuevo" class="block text-sm font-medium text-gray-700 mb-1">
                    Cambiar Estado (Opcional)
                </label>
                <select name="estado_nuevo" id="estado_nuevo"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="{{ $tarea->estado }}">Mantener estado actual ({{ ucfirst(str_replace('_', ' ', $tarea->estado)) }})</option>
                    <option value="pendiente" {{ $tarea->estado == 'pendiente' ? 'disabled' : '' }}>Pendiente</option>
                    <option value="en_progreso" {{ $tarea->estado == 'en_progreso' ? 'disabled' : '' }}>En Progreso</option>
                    <option value="bloqueada" {{ $tarea->estado == 'bloqueada' ? 'disabled' : '' }}>Bloqueada</option>
                    <option value="finalizada" {{ $tarea->estado == 'finalizada' ? 'disabled' : '' }}>Finalizada</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Solo el presidente puede cambiar el estado</p>
            </div>
            @else
            <input type="hidden" name="estado_nuevo" value="{{ $tarea->estado }}">
            @endif

            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold">
                <i class="fas fa-comment-dots mr-2"></i>
                Agregar Seguimiento
            </button>
        </form>
    </div>

    <!-- Subir Archivo -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Subir Archivo</h2>
        
        <form action="{{ route('admin.consejo-tareas.archivos', $tarea->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="archivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo <span class="text-red-500">*</span>
                </label>
                <input type="file" name="archivo" id="archivo" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('archivo') border-red-500 @enderror"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                @error('archivo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Tamaño máximo: 10MB</p>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
                <i class="fas fa-upload mr-2"></i>
                Subir Archivo
            </button>
        </form>
    </div>
</div>

<!-- Historial de Seguimientos -->
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Historial de Seguimientos</h2>
    
    @if($seguimientos->count() > 0)
    <div class="space-y-4">
        @foreach($seguimientos as $seguimiento)
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
    @else
    <p class="text-gray-500 text-center py-8">No hay seguimientos registrados</p>
    @endif
</div>

<!-- Archivos Adjuntos -->
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
    
    @if($archivos->count() > 0)
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
    @else
    <p class="text-gray-500 text-center py-8">No hay archivos adjuntos</p>
    @endif
</div>
@endsection
