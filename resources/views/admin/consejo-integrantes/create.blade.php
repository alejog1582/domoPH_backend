@extends('admin.layouts.app')

@section('title', 'Crear Integrante del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-integrantes.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Integrantes
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Crear Integrante del Consejo</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.consejo-integrantes.store') }}" method="POST" class="space-y-6">
    @csrf

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información del Integrante</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                    Teléfono
                </label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('telefono') border-red-500 @enderror">
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="unidad_apartamento" class="block text-sm font-medium text-gray-700 mb-1">
                    Unidad/Apartamento
                </label>
                <input type="text" name="unidad_apartamento" id="unidad_apartamento" value="{{ old('unidad_apartamento') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('unidad_apartamento') border-red-500 @enderror">
                @error('unidad_apartamento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">
                    Cargo <span class="text-red-500">*</span>
                </label>
                <select name="cargo" id="cargo" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cargo') border-red-500 @enderror">
                    <option value="">Seleccione un cargo</option>
                    <option value="presidente" {{ old('cargo') == 'presidente' ? 'selected' : '' }}>Presidente</option>
                    <option value="vicepresidente" {{ old('cargo') == 'vicepresidente' ? 'selected' : '' }}>Vicepresidente</option>
                    <option value="secretario" {{ old('cargo') == 'secretario' ? 'selected' : '' }}>Secretario</option>
                    <option value="vocal" {{ old('cargo') == 'vocal' ? 'selected' : '' }}>Vocal</option>
                </select>
                @error('cargo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <input type="checkbox" name="es_presidente" id="es_presidente" value="1" {{ old('es_presidente') ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="es_presidente" class="ml-2 block text-sm font-medium text-gray-900">
                        Es Presidente del Consejo
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Solo puede haber un integrante activo con esta bandera. Si marca esta opción y ya existe un presidente activo, se mostrará un error.</p>
                @error('es_presidente')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_inicio_periodo" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Inicio Periodo <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_inicio_periodo" id="fecha_inicio_periodo" value="{{ old('fecha_inicio_periodo') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio_periodo') border-red-500 @enderror">
                @error('fecha_inicio_periodo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_fin_periodo" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha Fin Periodo
                </label>
                <input type="date" name="fecha_fin_periodo" id="fecha_fin_periodo" value="{{ old('fecha_fin_periodo') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_fin_periodo') border-red-500 @enderror">
                @error('fecha_fin_periodo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center">
                <input type="checkbox" name="tiene_voz" id="tiene_voz" value="1" {{ old('tiene_voz', true) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="tiene_voz" class="ml-2 block text-sm text-gray-900">Tiene Voz</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="tiene_voto" id="tiene_voto" value="1" {{ old('tiene_voto', true) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="tiene_voto" class="ml-2 block text-sm text-gray-900">Tiene Voto</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="puede_convocar" id="puede_convocar" value="1" {{ old('puede_convocar') ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="puede_convocar" class="ml-2 block text-sm text-gray-900">Puede Convocar</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="puede_firmar_actas" id="puede_firmar_actas" value="1" {{ old('puede_firmar_actas') ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="puede_firmar_actas" class="ml-2 block text-sm text-gray-900">Puede Firmar Actas</label>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Módulos de Acceso</h2>
        <p class="text-sm text-gray-600 mb-4">Seleccione los módulos del Consejo a los que tendrá acceso este integrante:</p>
        
        <div class="space-y-3">
            @foreach($modulos as $modulo)
            <div class="flex items-center">
                <input type="checkbox" name="modulos[]" id="modulo_{{ $modulo->id }}" value="{{ $modulo->id }}"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="modulo_{{ $modulo->id }}" class="ml-2 block text-sm text-gray-900">
                    {{ $modulo->nombre }}
                </label>
            </div>
            @endforeach
        </div>
        @error('modulos')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.consejo-integrantes.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Crear Integrante
        </button>
    </div>
</form>
@endsection
