@extends('admin.layouts.app')

@section('title', 'Consejo - Integrantes')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Consejo – Integrantes</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de integrantes del Consejo de Administración</p>
        </div>
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-integrantes.create'))
        <a href="{{ route('admin.consejo-integrantes.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Integrante
        </a>
        @endif
    </div>
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

<!-- Filtros -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
    <form method="GET" action="{{ route('admin.consejo-integrantes.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input 
                    type="text" 
                    id="buscar" 
                    name="buscar" 
                    value="{{ request('buscar') }}" 
                    placeholder="Nombre, Email o Cargo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div>
                <label for="cargo" class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                <select 
                    id="cargo" 
                    name="cargo" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Todos</option>
                    <option value="presidente" {{ request('cargo') == 'presidente' ? 'selected' : '' }}>Presidente</option>
                    <option value="vicepresidente" {{ request('cargo') == 'vicepresidente' ? 'selected' : '' }}>Vicepresidente</option>
                    <option value="secretario" {{ request('cargo') == 'secretario' ? 'selected' : '' }}>Secretario</option>
                    <option value="vocal" {{ request('cargo') == 'vocal' ? 'selected' : '' }}>Vocal</option>
                </select>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>
                Buscar
            </button>
            <a href="{{ route('admin.consejo-integrantes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-gray-400 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                <i class="fas fa-redo mr-2"></i>
                Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Integrantes -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($integrantes as $integrante)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $integrante->nombre }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">{{ $integrante->email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($integrante->cargo) }}
                        </span>
                        @if($integrante->es_presidente ?? false)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800" title="Presidente del Consejo">
                            <i class="fas fa-crown mr-1"></i>Presidente
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $integrante->unidad_apartamento ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($integrante->fecha_inicio_periodo)->format('d/m/Y') }} - 
                    {{ $integrante->fecha_fin_periodo ? \Carbon\Carbon::parse($integrante->fecha_fin_periodo)->format('d/m/Y') : 'Indefinido' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        @if(\App\Helpers\AdminHelper::hasPermission('consejo-integrantes.view'))
                        <a href="{{ route('admin.consejo-integrantes.show', $integrante->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif
                        @if(\App\Helpers\AdminHelper::hasPermission('consejo-integrantes.edit'))
                        <a href="{{ route('admin.consejo-integrantes.edit', $integrante->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    No hay integrantes activos registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
@if($integrantes->hasPages())
<div class="mt-4">
    {{ $integrantes->links() }}
</div>
@endif
@endsection
