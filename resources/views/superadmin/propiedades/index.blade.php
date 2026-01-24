@extends('layouts.app')

@section('title', 'Propiedades - SuperAdmin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Propiedades</h1>
        <p class="mt-2 text-sm text-gray-600">Gestiona las copropiedades del sistema</p>
    </div>
    <a href="{{ route('superadmin.propiedades.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        <i class="fas fa-plus mr-2"></i> Nueva Propiedad
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('superadmin.propiedades.index') }}" class="flex gap-4">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Buscar por nombre, NIT o ciudad..." 
                class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <select name="estado" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                <option value="suspendida" {{ request('estado') == 'suspendida' ? 'selected' : '' }}>Suspendida</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            </select>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
        </form>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIT</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciudad</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($propiedades as $propiedad)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $propiedad->nombre }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $propiedad->nit ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $propiedad->ciudad }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $propiedad->plan->nombre ?? 'Sin plan' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $propiedad->estado == 'activa' ? 'bg-green-100 text-green-800' : 
                               ($propiedad->estado == 'suspendida' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($propiedad->estado) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('superadmin.propiedades.show', $propiedad) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('superadmin.propiedades.edit', $propiedad) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.propiedades.destroy', $propiedad) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta propiedad?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron propiedades
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $propiedades->links() }}
    </div>
</div>
@endsection
