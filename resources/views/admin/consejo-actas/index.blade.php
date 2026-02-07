@extends('admin.layouts.app')

@section('title', 'Actas de Reuniones')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Actas de Reuniones</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de actas del Consejo de Administración</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-actas.create'))
        <a href="{{ route('admin.consejo-actas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Acta
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
    <form method="GET" action="{{ route('admin.consejo-actas.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" id="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos</option>
                    <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="finalizada" {{ request('estado') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                    <option value="firmada" {{ request('estado') == 'firmada' ? 'selected' : '' }}>Firmada</option>
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
            <a href="{{ route('admin.consejo-actas.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Acta</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo Reunión</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quorum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Firmas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($actas as $acta)
            @php
                $reunion = \Illuminate\Support\Facades\DB::table('consejo_reuniones')->where('id', $acta->reunion_id)->first();
            @endphp
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $acta->tipo_reunion == 'ordinaria' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ ucfirst($acta->tipo_reunion) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($acta->quorum)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Sí
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            <i class="fas fa-times mr-1"></i>No
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($acta->estado == 'borrador')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Borrador</span>
                    @elseif($acta->estado == 'finalizada')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Finalizada</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Firmada</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center">
                        <i class="fas fa-signature mr-1"></i>
                        {{ $acta->firmas_count }} firma(s)
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        @if(\App\Helpers\AdminHelper::hasPermission('consejo-actas.view'))
                        <a href="{{ route('admin.consejo-actas.show', $acta->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif
                        @if($acta->puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-actas.edit'))
                        <a href="{{ route('admin.consejo-actas.edit', $acta->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @if($acta->puede_editar && \App\Helpers\AdminHelper::hasPermission('consejo-actas.delete'))
                        <form action="{{ route('admin.consejo-actas.destroy', $acta->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este acta?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                        @if($reunion)
                        <a href="{{ route('admin.consejo-reuniones.show', $reunion->id) }}" class="text-purple-600 hover:text-purple-900" title="Ver Reunión">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay actas registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($actas->hasPages())
<div class="mt-4">{{ $actas->links() }}</div>
@endif
@endsection
