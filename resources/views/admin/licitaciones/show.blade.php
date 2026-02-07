@extends('admin.layouts.app')

@section('title', 'Detalle de Licitación - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $licitacion->titulo }}</h1>
            <p class="mt-2 text-sm text-gray-600">Detalle de la licitación y ofertas recibidas</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.licitaciones.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
            @if(\App\Helpers\AdminHelper::hasPermission('licitaciones.edit'))
            <a href="{{ route('admin.licitaciones.edit', $licitacion->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            @endif
        </div>
    </div>
</div>

<!-- Información de la Licitación -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Licitación</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Estado</label>
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
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Categoría</label>
            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ ucfirst(str_replace('_', ' ', $licitacion->categoria)) }}
            </span>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Presupuesto Estimado</label>
            <p class="text-gray-900">
                {{ $licitacion->presupuesto_estimado ? '$' . number_format($licitacion->presupuesto_estimado, 2, ',', '.') : 'No especificado' }}
            </p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Fecha de Publicación</label>
            <p class="text-gray-900">{{ $licitacion->fecha_publicacion ? $licitacion->fecha_publicacion->format('d/m/Y') : 'No publicada' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Fecha de Cierre</label>
            <p class="text-gray-900">{{ $licitacion->fecha_cierre->format('d/m/Y') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Visible Públicamente</label>
            <p class="text-gray-900">{{ $licitacion->visible_publicamente ? 'Sí' : 'No' }}</p>
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
        <div class="text-gray-900 whitespace-pre-wrap">{{ $licitacion->descripcion }}</div>
    </div>

    <!-- Archivos adjuntos -->
    @if($licitacion->archivos->count() > 0)
    <div class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Archivos Adjuntos</label>
        <div class="space-y-2">
            @foreach($licitacion->archivos as $archivo)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-file mr-2 text-gray-500"></i>
                    <span class="text-sm text-gray-900">{{ $archivo->nombre_archivo }}</span>
                </div>
                <a href="{{ $archivo->url_archivo }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-download mr-1"></i>Descargar
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Ofertas Recibidas -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Ofertas Recibidas ({{ $licitacion->ofertas->count() }})</h2>
    </div>

    @if($licitacion->ofertas->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Ofertado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Postulación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($licitacion->ofertas as $oferta)
                <tr class="{{ $oferta->es_ganadora ? 'bg-green-50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $oferta->nombre_proveedor }}</div>
                        <div class="text-sm text-gray-500">{{ $oferta->email_contacto }}</div>
                        @if($oferta->nit_proveedor)
                        <div class="text-xs text-gray-400">NIT: {{ $oferta->nit_proveedor }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                        ${{ number_format($oferta->valor_ofertado, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoOfertaColors = [
                                'recibida' => 'bg-blue-100 text-blue-800',
                                'en_revision' => 'bg-yellow-100 text-yellow-800',
                                'seleccionada' => 'bg-green-100 text-green-800',
                                'rechazada' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $estadoOfertaColors[$oferta->estado] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst(str_replace('_', ' ', $oferta->estado)) }}
                        </span>
                        @if($oferta->es_ganadora)
                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-200 text-green-900">
                            Ganadora
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $oferta->fecha_postulacion->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="verOferta({{ $oferta->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-inbox text-4xl mb-4"></i>
        <p>No hay ofertas recibidas para esta licitación.</p>
    </div>
    @endif
</div>

<!-- Modal para ver oferta -->
<div id="modalOferta" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Detalle de Oferta</h3>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="contenidoOferta"></div>
    </div>
</div>

<script>
function verOferta(ofertaId) {
    fetch(`{{ url('/admin/ofertas') }}/${ofertaId}`)
        .then(response => response.json())
        .then(data => {
            const contenido = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Proveedor</label>
                        <p class="text-gray-900">${data.nombre_proveedor}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email de Contacto</label>
                        <p class="text-gray-900">${data.email_contacto}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <p class="text-gray-900">${data.telefono_contacto || 'No especificado'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIT</label>
                        <p class="text-gray-900">${data.nit_proveedor || 'No especificado'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Valor Ofertado</label>
                        <p class="text-gray-900 font-semibold">$${new Intl.NumberFormat('es-CO').format(data.valor_ofertado)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción de la Oferta</label>
                        <p class="text-gray-900 whitespace-pre-wrap">${data.descripcion_oferta}</p>
                    </div>
                    ${data.archivos && data.archivos.length > 0 ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Archivos Adjuntos</label>
                        <div class="space-y-2">
                            ${data.archivos.map(archivo => `
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <span class="text-sm text-gray-900">${archivo.nombre_archivo}</span>
                                    <a href="${archivo.url_archivo}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-download mr-1"></i>Descargar
                                    </a>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('contenidoOferta').innerHTML = contenido;
            document.getElementById('modalOferta').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la oferta');
        });
}

function cerrarModal() {
    document.getElementById('modalOferta').classList.add('hidden');
}
</script>
@endsection
