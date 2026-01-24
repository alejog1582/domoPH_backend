@extends('layouts.app')

@section('title', 'Editar Administrador - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Editar Administrador</h1>
    <p class="mt-2 text-sm text-gray-600">Modifica la información del administrador</p>
</div>

<form action="{{ route('superadmin.administradores.update', $administrador) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

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
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $administrador->nombre) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $administrador->email) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Nueva Contraseña
                </label>
                <input type="password" name="password" id="password"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Dejar en blanco para no cambiar la contraseña</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirmar Nueva Contraseña
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                    Teléfono
                </label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $administrador->telefono) }}"
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
                    <option value="CC" {{ old('tipo_documento', $administrador->tipo_documento) == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía (CC)</option>
                    <option value="CE" {{ old('tipo_documento', $administrador->tipo_documento) == 'CE' ? 'selected' : '' }}>Cédula de Extranjería (CE)</option>
                    <option value="NIT" {{ old('tipo_documento', $administrador->tipo_documento) == 'NIT' ? 'selected' : '' }}>NIT</option>
                    <option value="PASAPORTE" {{ old('tipo_documento', $administrador->tipo_documento) == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                </select>
                @error('tipo_documento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="documento_identidad" class="block text-sm font-medium text-gray-700 mb-2">
                    Número de Documento
                </label>
                <input type="text" name="documento_identidad" id="documento_identidad" value="{{ old('documento_identidad', $administrador->documento_identidad) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('documento_identidad') border-red-500 @enderror">
                @error('documento_identidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="activo" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select name="activo" id="activo" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('activo') border-red-500 @enderror">
                    <option value="1" {{ old('activo', $administrador->activo) == '1' || old('activo', $administrador->activo) == 1 ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ old('activo', $administrador->activo) == '0' || old('activo', $administrador->activo) == 0 ? 'selected' : '' }}>Inactivo</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Al inactivar, el administrador no podrá acceder al sistema</p>
                @error('activo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Propiedades Asociadas (Solo lectura) -->
    @if($administrador->administracionesPropiedad->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-building mr-2"></i> Propiedades Asociadas
        </h2>
        
        <div class="space-y-3">
            @foreach($administrador->administracionesPropiedad as $adminProp)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">
                                {{ $adminProp->propiedad->nombre }}
                                @if($adminProp->es_principal)
                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-star mr-1"></i> Principal
                                    </span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="mr-4">Inicio: {{ $adminProp->fecha_inicio ? $adminProp->fecha_inicio->format('d/m/Y') : 'N/A' }}</span>
                                @if($adminProp->fecha_fin)
                                    <span>Fin: {{ $adminProp->fecha_fin->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-green-600">Activo</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-sm text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>
            Las propiedades asociadas se gestionan desde la edición de cada propiedad.
        </p>
    </div>
    @endif

    <!-- Botones de Acción -->
    <div class="flex justify-end gap-4">
        <a href="{{ route('superadmin.administradores.index') }}" 
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Cancelar
        </a>
        <button type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-save mr-2"></i> Actualizar Administrador
        </button>
    </div>
</form>
@endsection
