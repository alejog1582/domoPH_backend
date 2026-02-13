@extends('admin.layouts.app')

@section('title', 'Comunicaciones de Cobranza - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Comunicaciones de Cobranza</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de comunicaciones automatizadas de cobranza por email y WhatsApp</p>
        </div>
        <div>
            @if(\App\Helpers\AdminHelper::hasPermission('comunicaciones-cobranza.create'))
            <a href="{{ route('admin.comunicaciones-cobranza.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Comunicación
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<!-- Tabla de Comunicaciones -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="table-container">
        <table class="table-domoph min-w-full">
            <thead>
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nombre
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Canal
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Día Envío
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Rango de Mora
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($comunicaciones as $comunicacion)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $comunicacion->nombre }}</div>
                            @if($comunicacion->descripcion)
                                <div class="text-sm text-gray-500">{{ Str::limit($comunicacion->descripcion, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $comunicacion->canal == 'ambos' ? 'bg-purple-100 text-purple-800' : ($comunicacion->canal == 'email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($comunicacion->canal) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Día {{ $comunicacion->dia_envio_mes }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($comunicacion->dias_mora_desde == 0 && $comunicacion->dias_mora_hasta == 0)
                                <span class="text-green-600">Sin mora (Preventivo)</span>
                            @elseif($comunicacion->dias_mora_hasta)
                                {{ $comunicacion->dias_mora_desde }} - {{ $comunicacion->dias_mora_hasta }} días
                            @else
                                {{ $comunicacion->dias_mora_desde }}+ días
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($comunicacion->activo)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Activo
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.comunicaciones-cobranza.show', $comunicacion->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" 
                                   title="Previsualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(\App\Helpers\AdminHelper::hasPermission('comunicaciones-cobranza.edit'))
                                <a href="{{ route('admin.comunicaciones-cobranza.edit', $comunicacion->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(\App\Helpers\AdminHelper::hasPermission('comunicaciones-cobranza.delete'))
                                <form action="{{ route('admin.comunicaciones-cobranza.destroy', $comunicacion->id) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('¿Está seguro de eliminar esta comunicación?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron comunicaciones de cobranza.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
