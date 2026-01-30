@extends('admin.layouts.app')

@section('title', 'Crear Comunicado - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Comunicado</h1>
            <p class="mt-2 text-sm text-gray-600">Crea un nuevo comunicado para la propiedad</p>
        </div>
        <a href="{{ route('admin.comunicados.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.comunicados.store') }}" method="POST">
        @csrf

        <!-- Título -->
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror">
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Resumen -->
        <div class="mb-4">
            <label for="resumen" class="block text-sm font-medium text-gray-700 mb-1">
                Resumen
            </label>
            <textarea name="resumen" id="resumen" rows="2" maxlength="500"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('resumen') border-red-500 @enderror">{{ old('resumen') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Máximo 500 caracteres</p>
            @error('resumen')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contenido -->
        <div class="mb-4">
            <label for="contenido" class="block text-sm font-medium text-gray-700 mb-1">
                Contenido <span class="text-red-500">*</span>
            </label>
            <textarea name="contenido" id="contenido" rows="15" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contenido') border-red-500 @enderror">{{ old('contenido') }}</textarea>
            @error('contenido')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Tipo -->
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select name="tipo" id="tipo" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="general" {{ old('tipo') == 'general' ? 'selected' : '' }}>General</option>
                    <option value="urgente" {{ old('tipo') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                    <option value="informativo" {{ old('tipo') == 'informativo' ? 'selected' : '' }}>Informativo</option>
                    <option value="mantenimiento" {{ old('tipo') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Visibilidad -->
            <div>
                <label for="visible_para" class="block text-sm font-medium text-gray-700 mb-1">
                    Visibilidad <span class="text-red-500">*</span>
                </label>
                <select name="visible_para" id="visible_para" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('visible_para') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="todos" {{ old('visible_para') == 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="propietarios" {{ old('visible_para') == 'propietarios' ? 'selected' : '' }}>Propietarios</option>
                    <option value="arrendatarios" {{ old('visible_para') == 'arrendatarios' ? 'selected' : '' }}>Arrendatarios</option>
                    <option value="administracion" {{ old('visible_para') == 'administracion' ? 'selected' : '' }}>Administración</option>
                </select>
                @error('visible_para')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Publicación -->
            <div>
                <label for="fecha_publicacion" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Publicación
                </label>
                <input type="datetime-local" name="fecha_publicacion" id="fecha_publicacion" value="{{ old('fecha_publicacion') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_publicacion') border-red-500 @enderror">
                @error('fecha_publicacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Publicado -->
            <div class="flex items-center">
                <input type="checkbox" name="publicado" id="publicado" value="1" {{ old('publicado') ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="publicado" class="ml-2 block text-sm text-gray-700">
                    Publicar inmediatamente
                </label>
            </div>
        </div>

        <!-- Imagen de Portada -->
        <div class="mb-4">
            <label for="imagen_portada" class="block text-sm font-medium text-gray-700 mb-1">
                URL de Imagen de Portada
            </label>
            <input type="url" name="imagen_portada" id="imagen_portada" value="{{ old('imagen_portada') }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('imagen_portada') border-red-500 @enderror"
                placeholder="https://ejemplo.com/imagen.jpg">
            @error('imagen_portada')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.comunicados.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Guardar Comunicado
            </button>
        </div>
    </form>
</div>
@endsection
