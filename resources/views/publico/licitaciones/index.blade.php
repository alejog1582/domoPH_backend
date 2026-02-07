@extends('publico.layouts.app')

@section('title', 'Cartelera de Licitaciones - ' . $propiedad->nombre)

@section('content')
<div class="mb-8">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
                @if($propiedad->logo)
                <div class="flex-shrink-0">
                    <img src="{{ $propiedad->logo }}" alt="{{ $propiedad->nombre }}" 
                         class="h-24 w-24 object-contain bg-white rounded-lg p-2">
                </div>
                @endif
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ $propiedad->nombre }}</h1>
                    <p class="text-blue-100 text-lg">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        {{ $propiedad->direccion }}
                    </p>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                    <p class="text-sm text-blue-100 mb-1">Licitaciones Activas</p>
                    <p class="text-3xl font-bold">{{ $licitaciones->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if($licitaciones->count() > 0)
    <div class="grid gap-6">
        @foreach($licitaciones as $licitacion)
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $licitacion->titulo }}</h2>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-tag mr-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $licitacion->categoria)) }}
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Activa
                            </span>
                        </div>
                    </div>
                </div>

                <p class="text-gray-700 mb-4 line-clamp-3">{{ $licitacion->descripcion }}</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                        <div>
                            <p class="text-xs text-gray-500">Fecha de Cierre</p>
                            <p class="font-semibold">{{ $licitacion->fecha_cierre->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    @if($licitacion->presupuesto_estimado)
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-dollar-sign mr-2 text-green-600"></i>
                        <div>
                            <p class="text-xs text-gray-500">Presupuesto Estimado</p>
                            <p class="font-semibold">${{ number_format($licitacion->presupuesto_estimado, 2, ',', '.') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($licitacion->archivos->count() > 0)
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-file mr-2 text-purple-600"></i>
                        <div>
                            <p class="text-xs text-gray-500">Documentos</p>
                            <p class="font-semibold">{{ $licitacion->archivos->count() }} archivo(s)</p>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('licitaciones-publicas.show', $licitacion->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        Ver Detalles
                    </a>
                    @if(\Carbon\Carbon::now()->lte($licitacion->fecha_cierre))
                    <a href="{{ route('licitaciones-publicas.create-oferta', $licitacion->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-hand-holding-usd mr-2"></i>
                        Enviar Oferta
                    </a>
                    @else
                    <span class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-600 rounded-md cursor-not-allowed">
                        <i class="fas fa-lock mr-2"></i>
                        Cerrada
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay licitaciones disponibles</h3>
        <p class="text-gray-600">Actualmente no hay licitaciones activas para esta propiedad.</p>
    </div>
@endif
@endsection
