@extends('admin.layouts.app')

@section('title', 'Configuración Cuotas Administración - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Configuración Cuotas Administración</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de cuotas ordinarias y extraordinarias</p>
        </div>
        <a href="{{ route('admin.cuotas-administracion.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Rubro
        </a>
    </div>
</div>

@if($propiedad)
    <!-- Sección de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>
        <form method="GET" action="{{ route('admin.cuotas-administracion.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Filtro Concepto -->
                <div>
                    <label for="concepto" class="block text-sm font-medium text-gray-700 mb-2">Concepto</label>
                    <select 
                        id="concepto" 
                        name="concepto" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos</option>
                        <option value="cuota_ordinaria" {{ request('concepto') == 'cuota_ordinaria' ? 'selected' : '' }}>Cuota Ordinaria</option>
                        <option value="cuota_extraordinaria" {{ request('concepto') == 'cuota_extraordinaria' ? 'selected' : '' }}>Cuota Extraordinaria</option>
                    </select>
                </div>

                <!-- Filtro Coeficiente -->
                <div>
                    <label for="coeficiente" class="block text-sm font-medium text-gray-700 mb-2">Coeficiente</label>
                    <input 
                        type="number" 
                        step="0.0001"
                        id="coeficiente" 
                        name="coeficiente" 
                        value="{{ request('coeficiente') }}" 
                        placeholder="Filtrar por coeficiente"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.cuotas-administracion.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times mr-1"></i> Limpiar filtros
                </a>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Cuotas -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CONCEPTO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">COEFICIENTE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">VALOR</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">MES DESDE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">MES HASTA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ESTADO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($cuotas as $cuota)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($cuota->concepto === 'cuota_ordinaria')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Cuota Ordinaria
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Cuota Extraordinaria
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cuota->coeficiente ? number_format($cuota->coeficiente, 4) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ number_format($cuota->valor, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cuota->mes_desde ? \Carbon\Carbon::parse($cuota->mes_desde)->format('d/m/Y') : 'Indefinido' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cuota->mes_hasta ? \Carbon\Carbon::parse($cuota->mes_hasta)->format('d/m/Y') : 'Indefinido' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($cuota->activo)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.cuotas-administracion.edit', $cuota->id) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        onclick="confirmarEliminacion({{ $cuota->id }}, '{{ $cuota->concepto === 'cuota_ordinaria' ? 'Cuota Ordinaria' : 'Cuota Extraordinaria' }}')" 
                                        class="text-red-600 hover:text-red-900" 
                                        title="Eliminar"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron cuotas de administración. 
                                @if(request('concepto') || request('coeficiente'))
                                    Intenta con otro criterio de búsqueda.
                                @else
                                    <a href="{{ route('admin.cuotas-administracion.create') }}" class="text-blue-600 hover:text-blue-800 underline">Crea un nuevo rubro</a> para comenzar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($cuotas->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $cuotas->links() }}
            </div>
        @endif
    </div>
@else
    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    No hay propiedad asignada
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Por favor, contacte al administrador para asignar una propiedad.</p>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modal de confirmación de eliminación -->
<div id="modalEliminar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-5">Confirmar Eliminación</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Está seguro de que desea eliminar esta cuota de administración?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="formEliminar" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Eliminar
                    </button>
                </form>
                <button onclick="cerrarModal()" class="mt-2 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id, nombre) {
    const form = document.getElementById('formEliminar');
    form.action = '{{ url("admin/cuotas-administracion") }}/' + id;
    document.getElementById('modalEliminar').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modalEliminar').classList.add('hidden');
}
</script>

@endsection
