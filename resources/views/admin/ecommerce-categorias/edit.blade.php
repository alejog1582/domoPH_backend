@extends('admin.layouts.app')

@section('title', 'Editar Categoría Ecommerce - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Categoría Ecommerce</h1>
            <p class="mt-2 text-sm text-gray-600">Modifica la información de la categoría</p>
        </div>
        <a href="{{ route('admin.ecommerce-categorias.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.ecommerce-categorias.update', $categoria->id) }}" method="POST">
        @csrf
        @method('PUT')

        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de la Categoría</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    value="{{ old('nombre', $categoria->nombre) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror"
                >
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Icono -->
            <div>
                <label for="icono" class="block text-sm font-medium text-gray-700 mb-2">
                    Icono (FontAwesome)
                </label>
                <input 
                    type="text" 
                    id="icono" 
                    name="icono" 
                    value="{{ old('icono', $categoria->icono) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('icono') border-red-500 @enderror"
                    placeholder="Ej: shopping-bag"
                >
                <p class="mt-1 text-xs text-gray-500">Nombre del icono de FontAwesome (sin prefijos)</p>
                @error('icono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea 
                    id="descripcion" 
                    name="descripcion" 
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror"
                >{{ old('descripcion', $categoria->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Activo -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="activo" 
                        value="1"
                        {{ old('activo', $categoria->activo) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Categoría activa</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.ecommerce-categorias.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Actualizar Categoría
            </button>
        </div>
    </form>
</div>
@endsection
