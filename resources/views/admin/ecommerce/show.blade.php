@extends('admin.layouts.app')

@section('title', 'Ver Publicación Ecommerce - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalles de la Publicación</h1>
            <p class="mt-2 text-sm text-gray-600">Información completa de la publicación</p>
        </div>
        <div class="flex items-center space-x-2">
            @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.edit'))
            <a href="{{ route('admin.ecommerce.edit', $publicacion->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.aprobar') && $publicacion->estado == 'en_revision')
            <form action="{{ route('admin.ecommerce.aprobar', $publicacion->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>
                    Aprobar
                </button>
            </form>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.pausar') && $publicacion->estado == 'publicado')
            <form action="{{ route('admin.ecommerce.pausar', $publicacion->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <i class="fas fa-pause mr-2"></i>
                    Pausar
                </button>
            </form>
            @endif
            @if(\App\Helpers\AdminHelper::hasPermission('ecommerce.finalizar') && $publicacion->estado != 'finalizado')
            <form action="{{ route('admin.ecommerce.finalizar', $publicacion->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-stop mr-2"></i>
                    Finalizar
                </button>
            </form>
            @endif
            <a href="{{ route('admin.ecommerce.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Título -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
            <p class="text-xl font-semibold text-gray-900">{{ $publicacion->titulo }}</p>
        </div>

        <!-- Categoría -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
            <p class="text-sm text-gray-900">{{ $publicacion->categoria_nombre }}</p>
        </div>

        <!-- Tipo -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            @php
                $tipoLabels = [
                    'venta' => 'Venta',
                    'arriendo' => 'Arriendo',
                    'servicio' => 'Servicio',
                    'otro' => 'Otro'
                ];
                $tipoLabel = $tipoLabels[$publicacion->tipo_publicacion] ?? ucfirst($publicacion->tipo_publicacion);
            @endphp
            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ $tipoLabel }}
            </span>
        </div>

        <!-- Residente -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Residente</label>
            <p class="text-sm text-gray-900">{{ $publicacion->residente_nombre ?? '-' }}</p>
        </div>

        <!-- Estado -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
            @php
                $estadoColors = [
                    'en_revision' => 'bg-orange-100 text-orange-800',
                    'publicado' => 'bg-green-100 text-green-800',
                    'pausado' => 'bg-yellow-100 text-yellow-800',
                    'finalizado' => 'bg-gray-100 text-gray-800',
                ];
                $color = $estadoColors[$publicacion->estado] ?? 'bg-gray-100 text-gray-800';
                $estadoLabels = [
                    'en_revision' => 'En Revisión',
                    'publicado' => 'Publicado',
                    'pausado' => 'Pausado',
                    'finalizado' => 'Finalizado',
                ];
                $estadoLabel = $estadoLabels[$publicacion->estado] ?? ucfirst($publicacion->estado);
            @endphp
            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                {{ $estadoLabel }}
            </span>
        </div>

        <!-- Precio -->
        @if($publicacion->precio)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Precio</label>
            <p class="text-lg font-semibold text-gray-900">
                ${{ number_format($publicacion->precio, 0, ',', '.') }} {{ $publicacion->moneda }}
                @if($publicacion->es_negociable)
                    <span class="text-sm text-gray-500 font-normal">(Negociable)</span>
                @endif
            </p>
        </div>
        @endif

        <!-- Fecha de Publicación -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Publicación</label>
            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($publicacion->fecha_publicacion)->format('d/m/Y H:i') }}</p>
        </div>

        @if($publicacion->fecha_cierre)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Cierre</label>
            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($publicacion->fecha_cierre)->format('d/m/Y H:i') }}</p>
        </div>
        @endif

        <!-- Descripción -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
            <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ $publicacion->descripcion ?? 'Sin descripción' }}</div>
        </div>
    </div>
</div>

<!-- Información de Contacto -->
@if($contactos->count() > 0)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Contacto</h3>
    <div class="space-y-4">
        @foreach($contactos as $contacto)
        <div class="border border-gray-200 rounded-md p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                    <p class="text-sm font-medium text-gray-900">{{ $contacto->nombre_contacto }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                    <p class="text-sm text-gray-900">
                        {{ $contacto->telefono }}
                        @if($contacto->whatsapp)
                            <span class="ml-2 text-green-600"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                        @endif
                    </p>
                </div>
                @if($contacto->email)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <p class="text-sm text-gray-900">{{ $contacto->email }}</p>
                </div>
                @endif
                @if($contacto->observaciones)
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Observaciones</label>
                    <p class="text-sm text-gray-900">{{ $contacto->observaciones }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Imágenes -->
@if($imagenes->count() > 0)
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Imágenes</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($imagenes as $imagen)
        <div class="relative">
            <img src="{{ $imagen->ruta_imagen }}" alt="Imagen {{ $loop->iteration }}" class="w-full h-32 object-cover rounded-md">
            @if($imagen->orden == 0)
                <span class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">Principal</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
