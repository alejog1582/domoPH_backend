@extends('admin.layouts.app')

@section('title', 'Consejo – Reuniones')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Consejo – Reuniones</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de reuniones del Consejo de Administración</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-reuniones.create'))
        <a href="{{ route('admin.consejo-reuniones.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Reunión
        </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
@endif

<!-- Filtros -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('admin.consejo-reuniones.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" id="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="programada" {{ request('estado') == 'programada' ? 'selected' : '' }}>Programada</option>
                    <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    <option value="realizada" {{ request('estado') == 'realizada' ? 'selected' : '' }}>Realizada</option>
                </select>
            </div>
            <div>
                <label for="tipo_reunion" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select name="tipo_reunion" id="tipo_reunion" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="ordinaria" {{ request('tipo_reunion') == 'ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                    <option value="extraordinaria" {{ request('tipo_reunion') == 'extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                </select>
            </div>
            <div>
                <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>
        <div class="mt-4 flex space-x-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
            <a href="{{ route('admin.consejo-reuniones.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                <i class="fas fa-redo mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Inicio</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($reuniones as $reunion)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $reunion->titulo }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $reunion->tipo_reunion == 'ordinaria' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ ucfirst($reunion->tipo_reunion) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($reunion->fecha_inicio)->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($reunion->estado == 'programada')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Programada</span>
                    @elseif($reunion->estado == 'realizada')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Realizada</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelada</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.consejo-reuniones.show', $reunion->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($reunion->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('consejo-reuniones.edit'))
                        <a href="{{ route('admin.consejo-reuniones.edit', $reunion->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @if($reunion->estado == 'programada' && \App\Helpers\AdminHelper::hasPermission('consejo-actas.create'))
                        <a href="{{ route('admin.consejo-actas.create', ['reunion_id' => $reunion->id]) }}" class="text-green-600 hover:text-green-900" title="Crear Acta">
                            <i class="fas fa-file-signature"></i>
                        </a>
                        @endif
                        @if($reunion->estado == 'realizada' && $reunion->tiene_acta && \App\Helpers\AdminHelper::hasPermission('consejo-actas.view'))
                        @php
                            $acta = \Illuminate\Support\Facades\DB::table('consejo_actas')->where('reunion_id', $reunion->id)->first();
                        @endphp
                        @if($acta)
                        <a href="{{ route('admin.consejo-actas.show', $acta->id) }}" class="text-purple-600 hover:text-purple-900" title="Ver Acta">
                            <i class="fas fa-file-alt"></i>
                        </a>
                        @endif
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay reuniones registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($reuniones->hasPages())
<div class="mt-4">{{ $reuniones->links() }}</div>
@endif
@endsection
