@extends('admin.layouts.app')

@section('title', 'Detalle Integrante del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-integrantes.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Integrantes
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Detalle del Integrante</h1>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Nombre</label>
            <p class="text-lg font-semibold text-gray-900">{{ $integrante->nombre }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
            <p class="text-lg text-gray-900">{{ $integrante->email }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Teléfono</label>
            <p class="text-lg text-gray-900">{{ $integrante->telefono ?? '-' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Unidad/Apartamento</label>
            <p class="text-lg text-gray-900">{{ $integrante->unidad_apartamento ?? '-' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Cargo</label>
            <div class="flex items-center space-x-2">
                <p class="text-lg font-semibold text-blue-600">{{ ucfirst($integrante->cargo) }}</p>
                @if($integrante->es_presidente ?? false)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    <i class="fas fa-crown mr-1"></i>Presidente
                </span>
                @endif
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Periodo</label>
            <p class="text-lg text-gray-900">
                {{ \Carbon\Carbon::parse($integrante->fecha_inicio_periodo)->format('d/m/Y') }} - 
                {{ $integrante->fecha_fin_periodo ? \Carbon\Carbon::parse($integrante->fecha_fin_periodo)->format('d/m/Y') : 'Indefinido' }}
            </p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Tiene Voz</p>
            <p class="text-lg font-semibold {{ $integrante->tiene_voz ? 'text-green-600' : 'text-red-600' }}">
                {{ $integrante->tiene_voz ? 'Sí' : 'No' }}
            </p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Tiene Voto</p>
            <p class="text-lg font-semibold {{ $integrante->tiene_voto ? 'text-green-600' : 'text-red-600' }}">
                {{ $integrante->tiene_voto ? 'Sí' : 'No' }}
            </p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Puede Convocar</p>
            <p class="text-lg font-semibold {{ $integrante->puede_convocar ? 'text-green-600' : 'text-red-600' }}">
                {{ $integrante->puede_convocar ? 'Sí' : 'No' }}
            </p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Puede Firmar Actas</p>
            <p class="text-lg font-semibold {{ $integrante->puede_firmar_actas ? 'text-green-600' : 'text-red-600' }}">
                {{ $integrante->puede_firmar_actas ? 'Sí' : 'No' }}
            </p>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-end space-x-3">
        @if(\App\Helpers\AdminHelper::hasPermission('consejo-integrantes.edit'))
        <a href="{{ route('admin.consejo-integrantes.edit', $integrante->id) }}" class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
            <i class="fas fa-edit mr-2"></i>
            Editar
        </a>
        @endif
    </div>
</div>
@endsection
