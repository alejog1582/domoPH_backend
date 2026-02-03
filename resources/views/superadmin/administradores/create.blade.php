@extends('layouts.app')

@section('title', 'Crear Administrador - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Crear Administrador</h1>
    <p class="mt-2 text-sm text-gray-600">Registra un nuevo administrador en el sistema</p>
</div>

<form action="{{ route('superadmin.administradores.store') }}" method="POST" class="space-y-6">
    @csrf

    <!-- Información Personal -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-user mr-2"></i> Información Personal
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Contraseña <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" id="password" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirmar Contraseña <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                    Teléfono
                </label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefono') border-red-500 @enderror">
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Documento
                </label>
                <select name="tipo_documento" id="tipo_documento"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo_documento') border-red-500 @enderror">
                    <option value="">Seleccione</option>
                    <option value="CC" {{ old('tipo_documento') == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía (CC)</option>
                    <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Cédula de Extranjería (CE)</option>
                    <option value="NIT" {{ old('tipo_documento') == 'NIT' ? 'selected' : '' }}>NIT</option>
                    <option value="PASAPORTE" {{ old('tipo_documento') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                </select>
                @error('tipo_documento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="documento_identidad" class="block text-sm font-medium text-gray-700 mb-2">
                    Número de Documento
                </label>
                <input type="text" name="documento_identidad" id="documento_identidad" value="{{ old('documento_identidad') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('documento_identidad') border-red-500 @enderror">
                @error('documento_identidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Propiedad Asociada -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-building mr-2"></i> Propiedad Asociada
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="propiedad_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Propiedad <span class="text-red-500">*</span>
                </label>
                <select name="propiedad_id" id="propiedad_id" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('propiedad_id') border-red-500 @enderror">
                    <option value="">Seleccione una propiedad</option>
                    @foreach($propiedades as $propiedad)
                        <option value="{{ $propiedad->id }}" {{ old('propiedad_id') == $propiedad->id ? 'selected' : '' }}>
                            {{ $propiedad->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('propiedad_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="es_principal" class="block text-sm font-medium text-gray-700 mb-2">
                    Administrador Principal
                </label>
                <div class="flex items-center">
                    <input type="checkbox" name="es_principal" id="es_principal" value="1" {{ old('es_principal') ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="es_principal" class="ml-2 block text-sm text-gray-700">
                        Marcar como administrador principal de la propiedad
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">El administrador principal tiene permisos adicionales</p>
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de Inicio
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_inicio') border-red-500 @enderror">
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de Fin (Opcional)
                </label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_fin') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Dejar en blanco si no tiene fecha de finalización</p>
                @error('fecha_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="flex justify-end gap-4">
        <a href="{{ route('superadmin.administradores.index') }}" 
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Cancelar
        </a>
        <button type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-save mr-2"></i> Crear Administrador
        </button>
    </div>
</form>
@endsection
