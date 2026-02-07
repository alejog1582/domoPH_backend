@extends('admin.layouts.app')

@section('title', 'Crear Decisión del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-decisiones.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Decisiones
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Crear Decisión del Consejo</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.consejo-decisiones.store') }}" method="POST" class="space-y-6">
    @csrf

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Decisión</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="acta_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Acta <span class="text-red-500">*</span>
                </label>
                <select name="acta_id" id="acta_id" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('acta_id') border-red-500 @enderror">
                    <option value="">Seleccione un acta</option>
                    @foreach($actas as $acta)
                    <option value="{{ $acta->id }}" 
                        {{ old('acta_id', $actaId) == $acta->id ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }} - {{ ucfirst($acta->tipo_reunion) }}
                    </option>
                    @endforeach
                </select>
                @error('acta_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Solo se muestran actas de los últimos 3 meses</p>
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción <span class="text-red-500">*</span>
                </label>
                <textarea name="descripcion" id="descripcion" rows="6" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror"
                    placeholder="Describa la decisión tomada en el consejo...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="responsable" class="block text-sm font-medium text-gray-700 mb-1">
                    Responsable
                </label>
                <input type="text" name="responsable" id="responsable" value="{{ old('responsable') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('responsable') border-red-500 @enderror"
                    placeholder="Nombre del responsable">
                @error('responsable')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_compromiso" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Compromiso
                </label>
                <input type="date" name="fecha_compromiso" id="fecha_compromiso" value="{{ old('fecha_compromiso') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_compromiso') border-red-500 @enderror">
                @error('fecha_compromiso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.consejo-decisiones.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Crear Decisión
        </button>
    </div>
</form>
@endsection
