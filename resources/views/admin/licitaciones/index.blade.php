@extends('admin.layouts.app')

@section('title', 'Cartelera de Licitaciones - Administrador')

@section('content')
@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times cursor-pointer"></i>
        </span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times cursor-pointer"></i>
        </span>
    </div>
@endif

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cartelera de Licitaciones</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de licitaciones y ofertas de proveedores</p>
        </div>
        <div class="flex gap-2">
            <button onclick="copiarLinkPublico()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                <i class="fas fa-link mr-2"></i>
                Copiar Link Público
            </button>
            @if(\App\Helpers\AdminHelper::hasPermission('licitaciones.create'))
            <a href="{{ route('admin.licitaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Crear Licitación
            </a>
            @endif
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.licitaciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                placeholder="Título, descripción..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                <option value="publicada" {{ request('estado') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                <option value="adjudicada" {{ request('estado') == 'adjudicada' ? 'selected' : '' }}>Adjudicada</option>
                <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
            </select>
        </div>
        <div>
            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
            <select name="categoria" id="categoria" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todas</option>
                <option value="mantenimiento" {{ request('categoria') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="seguridad" {{ request('categoria') == 'seguridad' ? 'selected' : '' }}>Seguridad</option>
                <option value="servicios" {{ request('categoria') == 'servicios' ? 'selected' : '' }}>Servicios</option>
                <option value="obra_civil" {{ request('categoria') == 'obra_civil' ? 'selected' : '' }}>Obra Civil</option>
                <option value="tecnologia" {{ request('categoria') == 'tecnologia' ? 'selected' : '' }}>Tecnología</option>
                <option value="otro" {{ request('categoria') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabla de Licitaciones -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Cierre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ofertas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($licitaciones as $licitacion)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $licitacion->titulo }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($licitacion->descripcion, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $licitacion->categoria)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoColors = [
                                'borrador' => 'bg-gray-100 text-gray-800',
                                'publicada' => 'bg-green-100 text-green-800',
                                'cerrada' => 'bg-yellow-100 text-yellow-800',
                                'adjudicada' => 'bg-blue-100 text-blue-800',
                                'anulada' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $estadoColors[$licitacion->estado] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($licitacion->estado) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $licitacion->fecha_cierre ? $licitacion->fecha_cierre->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="font-semibold">{{ $licitacion->ofertas->count() }}</span> oferta(s)
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.licitaciones.show', $licitacion->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(\App\Helpers\AdminHelper::hasPermission('licitaciones.edit'))
                            <a href="{{ route('admin.licitaciones.edit', $licitacion->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @if(\App\Helpers\AdminHelper::hasPermission('licitaciones.delete'))
                            <form action="{{ route('admin.licitaciones.destroy', $licitacion->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta licitación?');">
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
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No hay licitaciones registradas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $licitaciones->links() }}
    </div>
</div>

<!-- Mensaje de confirmación -->
<div id="mensajeCopiado" class="hidden fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>Link público copiado al portapapeles</span>
    </div>
</div>

<script>
function copiarLinkPublico() {
    const propiedadId = {{ $propiedad->id }};
    const urlPublica = window.location.origin + '/licitaciones-publicas/propiedad/' + propiedadId;
    
    // Crear un elemento temporal para copiar
    const tempInput = document.createElement('input');
    tempInput.value = urlPublica;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Para dispositivos móviles
    
    try {
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        // Mostrar mensaje de confirmación
        const mensaje = document.getElementById('mensajeCopiado');
        mensaje.classList.remove('hidden');
        
        setTimeout(() => {
            mensaje.classList.add('hidden');
        }, 3000);
    } catch (err) {
        document.body.removeChild(tempInput);
        console.error('Error al copiar:', err);
        alert('Error al copiar el link. Por favor, cópialo manualmente: ' + urlPublica);
    }
}
</script>
@endsection
