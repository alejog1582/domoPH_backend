@extends('publico.layouts.app')

@section('title', $licitacion->titulo . ' - Cartelera de Licitaciones')

@section('content')
<div class="mb-6">
    <a href="{{ route('licitaciones-publicas.index', $licitacion->copropiedad->id) }}" 
       class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Licitaciones
    </a>
</div>

<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-8 text-white">
        <div class="flex items-center gap-6 mb-4">
            @if($licitacion->copropiedad->logo)
            <div class="flex-shrink-0">
                <img src="{{ $licitacion->copropiedad->logo }}" alt="{{ $licitacion->copropiedad->nombre }}" 
                     class="h-20 w-20 object-contain bg-white rounded-lg p-2">
            </div>
            @endif
            <div class="flex-1">
                <h1 class="text-3xl font-bold mb-2">{{ $licitacion->titulo }}</h1>
                <p class="text-blue-100 text-sm mb-2">
                    <i class="fas fa-building mr-2"></i>
                    {{ $licitacion->copropiedad->nombre }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-4 mt-4">
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-white bg-opacity-20">
                <i class="fas fa-tag mr-1"></i>
                {{ ucfirst(str_replace('_', ' ', $licitacion->categoria)) }}
            </span>
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-500">
                <i class="fas fa-check-circle mr-1"></i>
                Activa
            </span>
        </div>
    </div>

    <div class="p-8">
        <!-- Información General -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Fecha de Publicación</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $licitacion->fecha_publicacion ? $licitacion->fecha_publicacion->format('d/m/Y') : 'No publicada' }}
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Fecha de Cierre</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $licitacion->fecha_cierre->format('d/m/Y') }}
                </p>
            </div>
            @if($licitacion->presupuesto_estimado)
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Presupuesto Estimado</p>
                <p class="text-lg font-semibold text-green-600">
                    ${{ number_format($licitacion->presupuesto_estimado, 2, ',', '.') }}
                </p>
            </div>
            @endif
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Propiedad</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $licitacion->copropiedad->nombre }}
                </p>
            </div>
        </div>

        <!-- Descripción -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Descripción</h2>
            <div class="prose max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $licitacion->descripcion }}</p>
            </div>
        </div>

        <!-- Archivos Adjuntos -->
        @if($licitacion->archivos->count() > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-file mr-2"></i>
                Documentos Adjuntos
            </h2>
            <div class="space-y-3">
                @foreach($licitacion->archivos as $archivo)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center">
                        <i class="fas fa-file-pdf text-red-600 text-2xl mr-4"></i>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $archivo->nombre_archivo }}</p>
                            <p class="text-sm text-gray-500">{{ $archivo->tipo_archivo ?? 'Archivo' }}</p>
                        </div>
                    </div>
                    <a href="{{ $archivo->url_archivo }}" target="_blank" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-download mr-2"></i>
                        Descargar
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Botón de Oferta -->
        <div class="border-t border-gray-200 pt-6">
            @if(\Carbon\Carbon::now()->lte($licitacion->fecha_cierre))
            <div class="flex justify-center">
                <a href="{{ route('licitaciones-publicas.create-oferta', $licitacion->id) }}" 
                   class="inline-flex items-center px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-lg font-semibold shadow-lg">
                    <i class="fas fa-hand-holding-usd mr-2"></i>
                    Enviar Oferta
                </a>
            </div>
            @else
            <div class="text-center">
                <div class="inline-flex items-center px-6 py-3 bg-gray-300 text-gray-600 rounded-lg">
                    <i class="fas fa-lock mr-2"></i>
                    Esta licitación ha cerrado
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
