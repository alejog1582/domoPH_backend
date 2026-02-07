@extends('admin.layouts.app')

@section('title', 'Crear Comunicación del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-comunicaciones.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Comunicaciones
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Crear Comunicación del Consejo</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.consejo-comunicaciones.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data" id="formComunicacion">
    @csrf

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Comunicación</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror"
                    placeholder="Ej: Información sobre nueva normativa">
                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select name="tipo" id="tipo" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tipo') border-red-500 @enderror">
                    <option value="">Seleccione un tipo</option>
                    <option value="informativa" {{ old('tipo') == 'informativa' ? 'selected' : '' }}>Informativa</option>
                    <option value="urgente" {{ old('tipo') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                    <option value="circular" {{ old('tipo') == 'circular' ? 'selected' : '' }}>Circular</option>
                    <option value="recordatorio" {{ old('tipo') == 'recordatorio' ? 'selected' : '' }}>Recordatorio</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="visible_para" class="block text-sm font-medium text-gray-700 mb-1">
                    Visible Para <span class="text-red-500">*</span>
                </label>
                <select name="visible_para" id="visible_para" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('visible_para') border-red-500 @enderror">
                    <option value="">Seleccione audiencia</option>
                    <option value="consejo" {{ old('visible_para') == 'consejo' ? 'selected' : '' }}>Consejo</option>
                    <option value="propietarios" {{ old('visible_para') == 'propietarios' ? 'selected' : '' }}>Propietarios</option>
                    <option value="residentes" {{ old('visible_para') == 'residentes' ? 'selected' : '' }}>Residentes</option>
                    <option value="todos" {{ old('visible_para') == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
                @error('visible_para')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Si selecciona "Propietarios", "Residentes" o "Todos", la comunicación se duplicará en el módulo de comunicados para el frontend.</p>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Contenido</h2>
        
        <div>
            <label for="contenido" class="block text-sm font-medium text-gray-700 mb-1">
                Contenido <span class="text-red-500">*</span>
            </label>
            <textarea name="contenido" id="contenido" rows="15"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contenido') border-red-500 @enderror"
                placeholder="Escriba el contenido de la comunicación aquí...">{{ old('contenido') }}</textarea>
            @error('contenido')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Archivos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
        
        <div>
            <label for="archivos" class="block text-sm font-medium text-gray-700 mb-1">
                Archivos (Opcional)
            </label>
            <input type="file" name="archivos[]" id="archivos" multiple
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('archivos') border-red-500 @enderror"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            @error('archivos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('archivos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Tamaño máximo por archivo: 10MB. Puede seleccionar múltiples archivos.</p>
        </div>

        <div id="archivos-preview" class="mt-4"></div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.consejo-comunicaciones.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" id="btnCrearComunicacion" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Crear Comunicación
        </button>
    </div>
</form>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/7x88fqj9jt1cd4s1iw33req3fx7k819a43eoi007vuoradp5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar TinyMCE
    tinymce.init({
        selector: '#contenido',
        height: 500,
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
        branding: false
    });

    // Sincronizar TinyMCE antes de enviar el formulario
    const formComunicacion = document.getElementById('formComunicacion');
    const btnCrearComunicacion = document.getElementById('btnCrearComunicacion');
    
    if (formComunicacion) {
        // Agregar listener al botón también
        if (btnCrearComunicacion) {
            btnCrearComunicacion.addEventListener('click', function(e) {
                // Sincronizar TinyMCE
                try {
                    const editor = tinymce.get('contenido');
                    if (editor) {
                        editor.save();
                    }
                } catch (error) {
                    console.error('Error al sincronizar TinyMCE:', error);
                }
            });
        }
        
        formComunicacion.addEventListener('submit', function(e) {
            // Sincronizar TinyMCE con el textarea
            try {
                const editor = tinymce.get('contenido');
                if (editor) {
                    editor.save();
                }
            } catch (error) {
                console.error('Error al sincronizar TinyMCE:', error);
            }
            
            // Validar contenido manualmente
            const contenido = document.getElementById('contenido').value;
            const contenidoLimpio = contenido.replace(/<[^>]*>/g, '').trim();
            if (!contenidoLimpio || contenidoLimpio === '') {
                e.preventDefault();
                alert('Por favor, ingrese el contenido de la comunicación.');
                // Intentar enfocar el editor de TinyMCE
                try {
                    const editor = tinymce.get('contenido');
                    if (editor) {
                        editor.focus();
                    }
                } catch (error) {
                    console.error('Error al enfocar TinyMCE:', error);
                }
                return false;
            }
            
            // Validar título
            const titulo = document.getElementById('titulo').value.trim();
            if (!titulo) {
                e.preventDefault();
                alert('Por favor, ingrese el título de la comunicación.');
                document.getElementById('titulo').focus();
                return false;
            }
            
            // Validar tipo
            const tipo = document.getElementById('tipo').value;
            if (!tipo) {
                e.preventDefault();
                alert('Por favor, seleccione el tipo de comunicación.');
                document.getElementById('tipo').focus();
                return false;
            }
            
            // Validar visible_para
            const visiblePara = document.getElementById('visible_para').value;
            if (!visiblePara) {
                e.preventDefault();
                alert('Por favor, seleccione para quién es visible la comunicación.');
                document.getElementById('visible_para').focus();
                return false;
            }
        });
    }

    // Preview de archivos seleccionados
    const archivosInput = document.getElementById('archivos');
    if (archivosInput) {
        archivosInput.addEventListener('change', function(e) {
            const preview = document.getElementById('archivos-preview');
            if (!preview) return;
            
            preview.innerHTML = '';
            
            if (e.target.files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-disc list-inside space-y-1';
                
                Array.from(e.target.files).forEach((file, index) => {
                    const item = document.createElement('li');
                    item.className = 'text-sm text-gray-700';
                    const size = (file.size / 1024 / 1024).toFixed(2);
                    item.textContent = `${file.name} (${size} MB)`;
                    list.appendChild(item);
                });
                
                preview.appendChild(list);
            }
        });
    }
});
</script>
@endsection
