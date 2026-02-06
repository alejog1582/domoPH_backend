@extends('admin.layouts.app')

@section('title', 'Crear Usuario Admin - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Usuario Admin</h1>
            <p class="mt-2 text-sm text-gray-600">Registra un nuevo usuario administrado por la propiedad</p>
        </div>
        <a href="{{ route('admin.usuarios-admin.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.usuarios-admin.store') }}" method="POST">
        @csrf

        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Usuario</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    value="{{ old('nombre') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror"
                >
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Contraseña <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    minlength="8"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirmar Contraseña <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required
                    minlength="8"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <!-- Documento -->
            <div>
                <label for="documento_identidad" class="block text-sm font-medium text-gray-700 mb-2">
                    Documento de Identidad <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="documento_identidad" 
                    name="documento_identidad" 
                    value="{{ old('documento_identidad') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('documento_identidad') border-red-500 @enderror"
                >
                @error('documento_identidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono -->
            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                    Teléfono <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="telefono" 
                    name="telefono" 
                    value="{{ old('telefono') }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('telefono') border-red-500 @enderror"
                >
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Documento -->
            <div>
                <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Documento <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo_documento" 
                    name="tipo_documento" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo_documento') border-red-500 @enderror"
                >
                    <option value="">Seleccione...</option>
                    <option value="CC" {{ old('tipo_documento') == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                    <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                    <option value="PA" {{ old('tipo_documento') == 'PA' ? 'selected' : '' }}>Pasaporte</option>
                    <option value="NIT" {{ old('tipo_documento') == 'NIT' ? 'selected' : '' }}>NIT</option>
                </select>
                @error('tipo_documento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Perfil -->
            <div>
                <label for="perfil" class="block text-sm font-medium text-gray-700 mb-2">
                    Perfil <span class="text-red-500">*</span>
                </label>
                <select 
                    id="perfil" 
                    name="perfil" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('perfil') border-red-500 @enderror"
                >
                    <option value="">Seleccione...</option>
                    <option value="residente" {{ old('perfil') == 'residente' ? 'selected' : '' }}>Residente</option>
                    <option value="porteria" {{ old('perfil') == 'porteria' ? 'selected' : '' }}>Portería</option>
                    <option value="proveedor" {{ old('perfil') == 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                </select>
                @error('perfil')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Activo -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="activo" 
                    name="activo" 
                    value="1"
                    {{ old('activo', true) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="activo" class="ml-2 block text-sm text-gray-900">
                    Usuario Activo
                </label>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-8">Módulos y Permisos</h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 mb-4">Seleccione los módulos que tendrá acceso este usuario. Se crearán los permisos correspondientes automáticamente.</p>
            
            @error('modulos')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modulos as $modulo)
                    <div class="flex items-start">
                        <input 
                            type="checkbox" 
                            id="modulo_{{ $modulo->id }}" 
                            name="modulos[]" 
                            value="{{ $modulo->id }}"
                            {{ in_array($modulo->id, old('modulos', [])) ? 'checked' : '' }}
                            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="modulo_{{ $modulo->id }}" class="ml-2 block text-sm text-gray-900">
                            <span class="font-medium">{{ $modulo->nombre }}</span>
                            @if($modulo->descripcion)
                                <span class="block text-xs text-gray-500 mt-1">{{ $modulo->descripcion }}</span>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.usuarios-admin.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
