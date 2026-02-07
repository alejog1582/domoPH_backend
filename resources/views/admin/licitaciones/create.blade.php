@extends('admin.layouts.app')

@section('title', 'Crear Licitación - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Licitación</h1>
            <p class="mt-2 text-sm text-gray-600">Crea una nueva licitación para la propiedad</p>
        </div>
        <a href="{{ route('admin.licitaciones.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.licitaciones.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Título -->
        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required maxlength="200"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror">
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                Descripción <span class="text-red-500">*</span>
            </label>
            <textarea name="descripcion" id="descripcion" rows="8" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Categoría -->
            <div>
                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">
                    Categoría <span class="text-red-500">*</span>
                </label>
                <select name="categoria" id="categoria" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('categoria') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="mantenimiento" {{ old('categoria') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="seguridad" {{ old('categoria') == 'seguridad' ? 'selected' : '' }}>Seguridad</option>
                    <option value="servicios" {{ old('categoria') == 'servicios' ? 'selected' : '' }}>Servicios</option>
                    <option value="obra_civil" {{ old('categoria') == 'obra_civil' ? 'selected' : '' }}>Obra Civil</option>
                    <option value="tecnologia" {{ old('categoria') == 'tecnologia' ? 'selected' : '' }}>Tecnología</option>
                    <option value="otro" {{ old('categoria') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('categoria')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Presupuesto Estimado -->
            <div>
                <label for="presupuesto_estimado" class="block text-sm font-medium text-gray-700 mb-1">
                    Presupuesto Estimado
                </label>
                <input type="number" name="presupuesto_estimado" id="presupuesto_estimado" value="{{ old('presupuesto_estimado') }}" 
                    step="0.01" min="0"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('presupuesto_estimado') border-red-500 @enderror">
                @error('presupuesto_estimado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Publicación -->
            <div>
                <label for="fecha_publicacion" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Publicación
                </label>
                <input type="date" name="fecha_publicacion" id="fecha_publicacion" value="{{ old('fecha_publicacion') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_publicacion') border-red-500 @enderror">
                @error('fecha_publicacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Cierre -->
            <div>
                <label for="fecha_cierre" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Cierre <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_cierre" id="fecha_cierre" value="{{ old('fecha_cierre') }}" required
                    min="{{ date('Y-m-d') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_cierre') border-red-500 @enderror">
                @error('fecha_cierre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select name="estado" id="estado" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estado') border-red-500 @enderror">
                    <option value="borrador" {{ old('estado', 'borrador') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="publicada" {{ old('estado') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                    <option value="cerrada" {{ old('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    <option value="adjudicada" {{ old('estado') == 'adjudicada' ? 'selected' : '' }}>Adjudicada</option>
                    <option value="anulada" {{ old('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Visible Públicamente -->
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="visible_publicamente" id="visible_publicamente" value="1" {{ old('visible_publicamente', true) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="visible_publicamente" class="ml-2 block text-sm text-gray-700">
                    Visible públicamente
                </label>
            </div>
        </div>

        <!-- Archivos -->
        <div class="mb-4">
            <label for="archivos" class="block text-sm font-medium text-gray-700 mb-1">
                Archivos Adjuntos (Pliegos, Términos de Referencia, etc.)
            </label>
            <input type="file" name="archivos[]" id="archivos" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('archivos.*') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG. Máximo 10MB por archivo.</p>
            @error('archivos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.licitaciones.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Guardar Licitación
            </button>
        </div>
    </form>
</div>
@endsection
