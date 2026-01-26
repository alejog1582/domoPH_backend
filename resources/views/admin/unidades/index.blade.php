@extends('admin.layouts.app')

@section('title', 'Unidades - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Unidades</h1>
    <p class="mt-2 text-sm text-gray-600">Gestión de unidades habitacionales</p>
</div>

@if($propiedad)
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600">Aquí se mostrará la lista de unidades de la propiedad: <strong>{{ $propiedad->nombre }}</strong></p>
        <!-- Aquí se puede agregar la tabla de unidades más adelante -->
    </div>
@else
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>No hay propiedad asignada. Contacta al superadministrador.</span>
        </div>
    </div>
@endif
@endsection
