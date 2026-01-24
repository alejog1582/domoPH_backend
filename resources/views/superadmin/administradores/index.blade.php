@extends('layouts.app')

@section('title', 'Administradores - SuperAdmin')

@section('content')
<div class="mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Administradores</h1>
        <p class="mt-2 text-sm text-gray-600">Gestiona los administradores de las propiedades</p>
        <p class="mt-1 text-xs text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>
            Los administradores solo se pueden crear al crear una propiedad. Aquí puedes editarlos e inactivarlos.
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('superadmin.administradores.index') }}" class="flex gap-4">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Buscar por nombre o email..." 
                class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <select name="propiedad_id" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas las propiedades</option>
                @foreach($propiedades as $propiedad)
                    <option value="{{ $propiedad->id }}" {{ request('propiedad_id') == $propiedad->id ? 'selected' : '' }}>
                        {{ $propiedad->nombre }}
                    </option>
                @endforeach
            </select>
            <select name="activo" class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
            </select>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
        </form>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propiedades</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($administradores as $administrador)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $administrador->nombre }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $administrador->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $administrador->telefono ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">
                            {{ $administrador->tipo_documento ?? '' }} {{ $administrador->documento_identidad ?? '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">
                            @if($administrador->administracionesPropiedad->count() > 0)
                                @foreach($administrador->administracionesPropiedad as $adminProp)
                                    <div class="mb-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $adminProp->es_principal ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $adminProp->propiedad->nombre }}
                                            @if($adminProp->es_principal)
                                                <i class="fas fa-star ml-1 text-xs"></i>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-gray-400">Sin propiedades</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $administrador->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $administrador->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('superadmin.administradores.edit', $administrador) }}" class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron administradores
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $administradores->links() }}
    </div>
</div>
@endsection
