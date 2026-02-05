@extends('admin.layouts.app')

@section('title', 'Manual de Convivencia - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manual de Convivencia</h1>
            <p class="mt-2 text-sm text-gray-600">Gestiona el manual de convivencia de la propiedad</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.manual-convivencia.store') }}" method="POST" enctype="multipart/form-data" id="manualForm">
        @csrf

        <!-- Archivo PDF del Manual -->
        <div class="mb-6">
            <label for="manual_file" class="block text-sm font-medium text-gray-700 mb-2">
                Manual de Convivencia (PDF)
            </label>
            
            @if($manual && $manual->manual_url)
                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-file-pdf text-red-600 text-2xl mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Manual actual</p>
                                <a href="{{ $manual->manual_url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                                    Ver manual actual <i class="fas fa-external-link-alt ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="manual_url" value="{{ $manual->manual_url }}">
            @endif

            <input type="file" name="manual_file" id="manual_file" accept=".pdf"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('manual_file') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">Formatos permitidos: PDF (máx. 10MB). Si subes un nuevo archivo, reemplazará el actual.</p>
            @error('manual_file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Principales Deberes -->
        <div class="mb-6">
            <label for="principales_deberes" class="block text-sm font-medium text-gray-700 mb-2">
                Principales Deberes
            </label>
            <textarea name="principales_deberes" id="principales_deberes" rows="10"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('principales_deberes') border-red-500 @enderror">{{ old('principales_deberes', $manual->principales_deberes ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Escribe los principales deberes de los residentes. Puedes usar formato HTML.</p>
            @error('principales_deberes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Principales Obligaciones -->
        <div class="mb-6">
            <label for="principales_obligaciones" class="block text-sm font-medium text-gray-700 mb-2">
                Principales Obligaciones
            </label>
            <textarea name="principales_obligaciones" id="principales_obligaciones" rows="10"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('principales_obligaciones') border-red-500 @enderror">{{ old('principales_obligaciones', $manual->principales_obligaciones ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Escribe las principales obligaciones de los residentes. Puedes usar formato HTML.</p>
            @error('principales_obligaciones')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Estado -->
        <div class="mb-6">
            <label class="flex items-center">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $manual->activo ?? true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Manual activo</span>
            </label>
            <p class="mt-1 text-xs text-gray-500">Si está desactivado, el manual no será visible para los residentes.</p>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-save mr-2"></i>Guardar Manual
            </button>
        </div>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/7x88fqj9jt1cd4s1iw33req3fx7k819a43eoi007vuoradp5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de TinyMCE
    tinymce.init({
        selector: '#principales_deberes, #principales_obligaciones',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        language: 'es',
        branding: false,
        promotion: false
    });
});
</script>
@endsection
