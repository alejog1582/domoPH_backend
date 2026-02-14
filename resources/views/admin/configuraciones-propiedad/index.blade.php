@extends('admin.layouts.app')

@section('title', 'Configuraciones Propiedad - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Configuraciones de la Propiedad</h1>
            <p class="mt-2 text-sm text-gray-600">Gestiona las configuraciones específicas de la propiedad</p>
        </div>
    </div>
</div>

@if($propiedad)
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Formulario de Configuraciones -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form method="POST" action="{{ route('admin.configuraciones-propiedad.update-multiple') }}" id="configuracionesForm" onsubmit="return guardarConfiguraciones(event);">
            @csrf
            @method('POST')
            
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Configuraciones Disponibles</h2>
                <p class="text-sm text-gray-600 mt-1">Activa o desactiva las configuraciones según tus necesidades</p>
            </div>

            <div class="divide-y divide-gray-200">
                @php
                    // Separar configuraciones de parqueaderos de visitantes
                    $configsParqVisitantes = $configuraciones->filter(function($config) {
                        return in_array($config->clave, ['cobro_parq_visitantes', 'valor_minuto_parq_visitantes', 'minutos_gracia_parq_visitantes']);
                    });
                    $otrasConfiguraciones = $configuraciones->filter(function($config) {
                        return !in_array($config->clave, ['cobro_parq_visitantes', 'valor_minuto_parq_visitantes', 'minutos_gracia_parq_visitantes']);
                    });
                @endphp
                
                @forelse($otrasConfiguraciones as $config)
                    @if($config->tipo === 'boolean')
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="text-base font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $config->clave)) }}</h3>
                                        @if($config->descripcion)
                                            <span class="ml-2 text-xs text-gray-500">
                                                <i class="fas fa-info-circle" title="{{ $config->descripcion }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                    @if($config->descripcion)
                                        <p class="mt-1 text-sm text-gray-600">{{ $config->descripcion }}</p>
                                    @endif
                                    @if($config->clave === 'cobro_parq_visitantes')
                                        @php
                                            $valorMinuto = $configuraciones->where('clave', 'valor_minuto_parq_visitantes')->first()->valor ?? '0';
                                            $puedeHabilitar = floatval($valorMinuto) > 0;
                                        @endphp
                                        @if(!$puedeHabilitar)
                                            <p class="mt-1 text-xs text-yellow-600">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Para habilitar el cobro, el valor por minuto debe ser mayor a 0.
                                            </p>
                                        @endif
                                    @endif
                                    <p class="mt-1 text-xs text-gray-500">
                                        <span class="font-medium">Tipo:</span> {{ $config->tipo }}
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        @php
                                            $valorMinuto = $config->clave === 'cobro_parq_visitantes' 
                                                ? ($configuraciones->where('clave', 'valor_minuto_parq_visitantes')->first()->valor ?? '0')
                                                : '1';
                                            $puedeHabilitar = $config->clave === 'cobro_parq_visitantes' 
                                                ? (floatval($valorMinuto) > 0)
                                                : true;
                                        @endphp
                                        <input 
                                            type="checkbox" 
                                            name="configuraciones[{{ $config->id }}]" 
                                            value="1"
                                            {{ $config->valor === 'true' ? 'checked' : '' }}
                                            {{ !$puedeHabilitar ? 'disabled' : '' }}
                                            class="sr-only peer"
                                            onchange="enviarFormulario();"
                                        >
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 {{ !$puedeHabilitar ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @elseif($config->tipo === 'html' || $config->tipo === 'text')
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors border-b border-gray-200">
                            <div class="mb-4">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-base font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $config->clave)) }}</h3>
                                    @if($config->descripcion)
                                        <span class="ml-2 text-xs text-gray-500">
                                            <i class="fas fa-info-circle" title="{{ $config->descripcion }}"></i>
                                        </span>
                                    @endif
                                </div>
                                @if($config->descripcion)
                                    <p class="text-sm text-gray-600 mb-3">{{ $config->descripcion }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mb-3">
                                    <span class="font-medium">Tipo:</span> {{ $config->tipo }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <textarea 
                                    name="configuraciones_html[{{ $config->id }}]" 
                                    id="editor_{{ $config->id }}" 
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    rows="10"
                                >{{ old('configuraciones_html.' . $config->id, $config->valor ?? '') }}</textarea>
                            </div>
                        </div>
                    @elseif($config->tipo === 'number')
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors border-b border-gray-200">
                            <div class="mb-4">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-base font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $config->clave)) }}</h3>
                                    @if($config->descripcion)
                                        <span class="ml-2 text-xs text-gray-500">
                                            <i class="fas fa-info-circle" title="{{ $config->descripcion }}"></i>
                                        </span>
                                    @endif
                                </div>
                                @if($config->descripcion)
                                    <p class="text-sm text-gray-600 mb-3">{{ $config->descripcion }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mb-3">
                                    <span class="font-medium">Tipo:</span> {{ $config->tipo }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <input 
                                    type="number" 
                                    name="configuraciones_number[{{ $config->id }}]" 
                                    value="{{ old('configuraciones_number.' . $config->id, $config->valor ?? '0') }}"
                                    min="0"
                                    step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('configuraciones_number.' . $config->id) border-red-500 @enderror"
                                >
                                @error('configuraciones_number.' . $config->id)
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="px-6 py-4 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="text-base font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $config->clave)) }}</h3>
                                        @if($config->descripcion)
                                            <span class="ml-2 text-xs text-gray-500">
                                                <i class="fas fa-info-circle" title="{{ $config->descripcion }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                    @if($config->descripcion)
                                        <p class="mt-1 text-sm text-gray-600">{{ $config->descripcion }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-500">
                                        <span class="font-medium">Tipo:</span> {{ $config->tipo }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-700">
                                        <span class="font-medium">Valor:</span> {{ $config->valor ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Solo lectura
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                @endforelse
                
                @if($configsParqVisitantes->count() > 0)
                    <!-- Sección de Configuraciones de Parqueaderos de Visitantes -->
                    <div class="px-6 py-4 bg-blue-50 border-t-2 border-blue-200">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-car text-blue-600 text-xl mr-3"></i>
                            <h2 class="text-lg font-semibold text-blue-900">Configuraciones de Parqueaderos de Visitantes</h2>
                        </div>
                        <p class="text-sm text-blue-700 mb-4">Configura los parámetros para el cobro de parqueaderos de visitantes</p>
                        
                        <div class="space-y-4 bg-white rounded-lg p-4 border border-blue-200">
                            @php
                                $cobroParq = $configsParqVisitantes->where('clave', 'cobro_parq_visitantes')->first();
                                $valorMinuto = $configsParqVisitantes->where('clave', 'valor_minuto_parq_visitantes')->first();
                                $minutosGracia = $configsParqVisitantes->where('clave', 'minutos_gracia_parq_visitantes')->first();
                                $puedeHabilitar = $valorMinuto && floatval($valorMinuto->valor) > 0;
                            @endphp
                            
                            <!-- Campo: Cobro parq visitantes -->
                            @if($cobroParq)
                                <div class="pb-4 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="text-base font-medium text-gray-900">Cobro Parqueaderos de Visitantes</h3>
                                                @if($cobroParq->descripcion)
                                                    <span class="ml-2 text-xs text-gray-500">
                                                        <i class="fas fa-info-circle" title="{{ $cobroParq->descripcion }}"></i>
                                                    </span>
                                                @endif
                                            </div>
                                            @if($cobroParq->descripcion)
                                                <p class="text-sm text-gray-600 mb-2">{{ $cobroParq->descripcion }}</p>
                                            @endif
                                            @if(!$puedeHabilitar)
                                                <p class="text-xs text-yellow-600 mb-2">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Para habilitar el cobro, el valor por minuto debe ser mayor a 0.
                                                </p>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input 
                                                    type="checkbox" 
                                                    name="configuraciones[{{ $cobroParq->id }}]" 
                                                    value="1"
                                                    {{ $cobroParq->valor === 'true' ? 'checked' : '' }}
                                                    {{ !$puedeHabilitar ? 'disabled' : '' }}
                                                    class="sr-only peer"
                                                    onchange="enviarFormulario();"
                                                >
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 {{ !$puedeHabilitar ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Campo: Valor minuto parq visitantes -->
                            @if($valorMinuto)
                                <div class="pb-4 border-b border-gray-200">
                                    <div class="mb-2">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-base font-medium text-gray-900">Valor por Minuto</h3>
                                            @if($valorMinuto->descripcion)
                                                <span class="ml-2 text-xs text-gray-500">
                                                    <i class="fas fa-info-circle" title="{{ $valorMinuto->descripcion }}"></i>
                                                </span>
                                            @endif
                                        </div>
                                        @if($valorMinuto->descripcion)
                                            <p class="text-sm text-gray-600 mb-2">{{ $valorMinuto->descripcion }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <input 
                                            type="number" 
                                            name="configuraciones_number[{{ $valorMinuto->id }}]" 
                                            value="{{ old('configuraciones_number.' . $valorMinuto->id, $valorMinuto->valor ?? '0') }}"
                                            min="0"
                                            step="0.01"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('configuraciones_number.' . $valorMinuto->id) border-red-500 @enderror"
                                        >
                                        @error('configuraciones_number.' . $valorMinuto->id)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Campo: Minutos gracia parq visitantes -->
                            @if($minutosGracia)
                                <div>
                                    <div class="mb-2">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-base font-medium text-gray-900">Minutos de Gracia</h3>
                                            @if($minutosGracia->descripcion)
                                                <span class="ml-2 text-xs text-gray-500">
                                                    <i class="fas fa-info-circle" title="{{ $minutosGracia->descripcion }}"></i>
                                                </span>
                                            @endif
                                        </div>
                                        @if($minutosGracia->descripcion)
                                            <p class="text-sm text-gray-600 mb-2">{{ $minutosGracia->descripcion }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <input 
                                            type="number" 
                                            name="configuraciones_number[{{ $minutosGracia->id }}]" 
                                            value="{{ old('configuraciones_number.' . $minutosGracia->id, $minutosGracia->valor ?? '0') }}"
                                            min="0"
                                            step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('configuraciones_number.' . $minutosGracia->id) border-red-500 @enderror"
                                        >
                                        @error('configuraciones_number.' . $minutosGracia->id)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($otrasConfiguraciones->count() === 0 && $configsParqVisitantes->count() === 0)
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-cog text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">No hay configuraciones disponibles para esta propiedad.</p>
                    </div>
                @endif
            </div>

            @if($configuraciones->where('tipo', 'boolean')->count() > 0 || $configuraciones->whereIn('tipo', ['html', 'text'])->count() > 0 || $configuraciones->where('tipo', 'number')->count() > 0)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            @if($configuraciones->where('tipo', 'boolean')->count() > 0)
                                Los cambios boolean se guardan automáticamente al activar o desactivar cada configuración.
                            @endif
                            @if($configuraciones->whereIn('tipo', ['html', 'text'])->count() > 0)
                                Guarda los cambios en los campos de texto enriquecido haciendo clic en el botón.
                            @endif
                            @if($configuraciones->where('tipo', 'number')->count() > 0)
                                Guarda los cambios en los campos numéricos haciendo clic en el botón.
                            @endif
                        </p>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            @endif
        </form>
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center py-12">
            <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
            <p class="text-gray-600">No hay propiedad asignada.</p>
        </div>
    </div>
@endif

@push('scripts')
<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/7x88fqj9jt1cd4s1iw33req3fx7k819a43eoi007vuoradp5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    let guardando = false;
    
    // Inicializar TinyMCE para todos los textareas de tipo html
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($configuraciones as $config)
            @if($config->tipo === 'html')
                tinymce.init({
                    selector: '#editor_{{ $config->id }}',
                    height: 400,
                    menubar: true,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                        'bold italic forecolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | ' +
                        'removeformat | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    language: 'es',
                    branding: false,
                    promotion: false
                });
            @endif
        @endforeach
    });
    
    function enviarFormulario() {
        if (guardando) return;
        guardando = true;
        
        // Guardar contenido de todos los editores TinyMCE antes de enviar
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        
        // Mostrar indicador de carga
        const form = document.getElementById('configuracionesForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';
        }
        
        // Enviar el formulario
        form.submit();
    }
    
    function guardarConfiguraciones(event) {
        if (guardando) {
            event.preventDefault();
            return false;
        }
        
        // Guardar contenido de todos los editores TinyMCE antes de enviar
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        
        guardando = true;
        return true;
    }
</script>
@endpush

@endsection
