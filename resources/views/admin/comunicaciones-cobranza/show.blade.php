@extends('admin.layouts.app')

@section('title', 'Previsualizar Comunicación de Cobranza - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Previsualizar Comunicación</h1>
            <p class="mt-2 text-sm text-gray-600">Vista previa de cómo se verá la comunicación para el residente</p>
        </div>
        <a href="{{ route('admin.comunicaciones-cobranza.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Comunicación</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-600">Nombre:</p>
            <p class="text-base font-medium text-gray-900">{{ $comunicacion->nombre }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Canal:</p>
            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                {{ $comunicacion->canal == 'ambos' ? 'bg-purple-100 text-purple-800' : ($comunicacion->canal == 'email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                {{ ucfirst($comunicacion->canal) }}
            </span>
        </div>
        <div>
            <p class="text-sm text-gray-600">Día de Envío:</p>
            <p class="text-base font-medium text-gray-900">Día {{ $comunicacion->dia_envio_mes }} del mes</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Rango de Mora:</p>
            <p class="text-base font-medium text-gray-900">
                @if($comunicacion->dias_mora_desde == 0 && $comunicacion->dias_mora_hasta == 0)
                    Sin mora (Preventivo)
                @elseif($comunicacion->dias_mora_hasta)
                    {{ $comunicacion->dias_mora_desde }} - {{ $comunicacion->dias_mora_hasta }} días
                @else
                    {{ $comunicacion->dias_mora_desde }}+ días
                @endif
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Estado:</p>
            @if($comunicacion->activo)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactivo</span>
            @endif
        </div>
    </div>
    @if($comunicacion->descripcion)
    <div class="mt-4">
        <p class="text-sm text-gray-600">Descripción:</p>
        <p class="text-base text-gray-900">{{ $comunicacion->descripcion }}</p>
    </div>
    @endif
</div>

<!-- Previsualización Email -->
@if($comunicacion->canal == 'email' || $comunicacion->canal == 'ambos')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-envelope mr-2 text-blue-600"></i>Previsualización Email
        </h2>
    </div>
    
    <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-1">De:</p>
            <p class="text-base font-medium text-gray-900">{{ $propiedad->nombre }} &lt;{{ $propiedad->email ?? 'noreply@domoph.com' }}&gt;</p>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-1">Para:</p>
            <p class="text-base font-medium text-gray-900">
                {{ $residenteEjemplo ? $residenteEjemplo->nombre_residente : 'Nombre del Residente' }} 
                &lt;{{ $residenteEjemplo ? strtolower(str_replace(' ', '.', $residenteEjemplo->nombre_residente)) . '@example.com' : 'residente@example.com' }}&gt;
            </p>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-1">Asunto:</p>
            <p class="text-base font-medium text-gray-900">{{ $comunicacion->asunto ?? 'Sin asunto' }}</p>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="prose max-w-none">
                {!! $mensajeEmailPreview ?? 'No hay mensaje configurado' !!}
            </div>
        </div>
    </div>
</div>
@endif

<!-- Previsualización WhatsApp -->
@if($comunicacion->canal == 'whatsapp' || $comunicacion->canal == 'ambos')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">
            <i class="fab fa-whatsapp mr-2 text-green-600"></i>Previsualización WhatsApp
        </h2>
    </div>
    
    <div class="flex justify-start">
        <div class="max-w-md">
            <div class="bg-green-100 rounded-lg p-4 shadow-sm">
                <div class="mb-2">
                    <p class="text-xs text-gray-600 mb-1">De: {{ $propiedad->nombre }}</p>
                    <p class="text-xs text-gray-600">Para: {{ $residenteEjemplo ? $residenteEjemplo->nombre_residente : 'Nombre del Residente' }} ({{ $residenteEjemplo ? $residenteEjemplo->unidad : 'Unidad' }})</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="prose max-w-none text-sm">
                        {!! $mensajeWhatsappPreview ?? 'No hay mensaje configurado' !!}
                    </div>
                </div>
                <div class="mt-2 flex items-center text-xs text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span>{{ now()->format('H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Información de Variables -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <h3 class="text-sm font-semibold text-blue-900 mb-2">
        <i class="fas fa-info-circle mr-2"></i>Variables Dinámicas Utilizadas
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-blue-800">
        <div><code>{nombre_residente}</code> → {{ $residenteEjemplo ? $residenteEjemplo->nombre_residente : 'Nombre del Residente' }}</div>
        <div><code>{unidad}</code> → {{ $residenteEjemplo ? $residenteEjemplo->unidad : 'Unidad' }}</div>
        <div><code>{saldo}</code> → $850.000</div>
        <div><code>{fecha_vencimiento}</code> → {{ now()->format('d/m/Y') }}</div>
        <div><code>{copropiedad}</code> → {{ $propiedad->nombre }}</div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.comunicaciones-cobranza.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
        Volver al Listado
    </a>
    @if(\App\Helpers\AdminHelper::hasPermission('comunicaciones-cobranza.edit'))
    <a href="{{ route('admin.comunicaciones-cobranza.edit', $comunicacion->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
        <i class="fas fa-edit mr-2"></i>Editar
    </a>
    @endif
</div>
@endsection
