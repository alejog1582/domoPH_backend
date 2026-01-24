@extends('layouts.app')

@section('title', 'Crear Módulo - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Módulo</h1>
    <p class="mt-2 text-sm text-gray-600">Completa los campos para crear un nuevo módulo</p>
</div>

<form action="{{ route('superadmin.modulos.store') }}" method="POST" class="space-y-6">
    @csrf

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-puzzle-piece mr-2"></i> Información del Módulo
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                    Slug <span class="text-red-500">*</span>
                </label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror"
                    placeholder="ej: nombre-del-modulo">
                <p class="mt-1 text-xs text-gray-500">URL amigable (sin espacios, solo minúsculas y guiones)</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea name="descripcion" id="descripcion" rows="3"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="icono" class="block text-sm font-medium text-gray-700 mb-2">
                    Icono
                </label>
                <input type="text" name="icono" id="icono" value="{{ old('icono') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('icono') border-red-500 @enderror"
                    placeholder="ej: dashboard, users, settings">
                <p class="mt-1 text-xs text-gray-500">Nombre del icono de Font Awesome (sin el prefijo "fa-")</p>
                @error('icono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ruta" class="block text-sm font-medium text-gray-700 mb-2">
                    Ruta
                </label>
                <input type="text" name="ruta" id="ruta" value="{{ old('ruta') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ruta') border-red-500 @enderror"
                    placeholder="/ruta-del-modulo">
                @error('ruta')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="orden" class="block text-sm font-medium text-gray-700 mb-2">
                    Orden
                </label>
                <input type="number" name="orden" id="orden" value="{{ old('orden', 0) }}" min="0"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('orden') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Orden de visualización (menor número = primero)</p>
                @error('orden')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="activo" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado
                </label>
                <select name="activo" id="activo"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('activo') border-red-500 @enderror">
                    <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ old('activo') == '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('activo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="requiere_configuracion" class="block text-sm font-medium text-gray-700 mb-2">
                    Requiere Configuración
                </label>
                <select name="requiere_configuracion" id="requiere_configuracion"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('requiere_configuracion') border-red-500 @enderror">
                    <option value="0" {{ old('requiere_configuracion', '0') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('requiere_configuracion') == '1' ? 'selected' : '' }}>Sí</option>
                </select>
                @error('requiere_configuracion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="configuracion_default" class="block text-sm font-medium text-gray-700 mb-2">
                    Configuración por Defecto (JSON)
                </label>
                <textarea name="configuracion_default" id="configuracion_default" rows="6"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('configuracion_default') border-red-500 @enderror"
                    placeholder='{"clave": "valor"}'>{{ old('configuracion_default') ? (is_string(old('configuracion_default')) ? old('configuracion_default') : json_encode(old('configuracion_default'), JSON_PRETTY_PRINT)) : '' }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Configuración JSON por defecto para este módulo</p>
                @error('configuracion_default')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="flex justify-end gap-4">
        <a href="{{ route('superadmin.modulos.index') }}" 
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Cancelar
        </a>
        <button type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-save mr-2"></i> Crear Módulo
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Auto-generar slug desde el nombre
    document.getElementById('nombre').addEventListener('input', function(e) {
        const slugInput = document.getElementById('slug');
        if (!slugInput.dataset.manual) {
            const slug = e.target.value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            slugInput.value = slug;
        }
    });

    // Permitir edición manual del slug
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manual = 'true';
    });
</script>
@endpush
@endsection
