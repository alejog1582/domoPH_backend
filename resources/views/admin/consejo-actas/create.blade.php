@extends('admin.layouts.app')

@section('title', 'Crear Acta de Reunión')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-actas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Actas
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Crear Acta de Reunión</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.consejo-actas.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data" id="formActa">
    @csrf

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información del Acta</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="reunion_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Reunión <span class="text-red-500">*</span>
                </label>
                <select name="reunion_id" id="reunion_id" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reunion_id') border-red-500 @enderror">
                    <option value="">Seleccione una reunión</option>
                    @foreach($reuniones as $reunion)
                    <option value="{{ $reunion->id }}" 
                        {{ old('reunion_id', $reunionId) == $reunion->id ? 'selected' : '' }}>
                        {{ $reunion->titulo }} - {{ \Carbon\Carbon::parse($reunion->fecha_inicio)->format('d/m/Y H:i') }}
                        ({{ ucfirst($reunion->tipo_reunion) }})
                    </option>
                    @endforeach
                </select>
                @error('reunion_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Solo se muestran reuniones de los últimos 3 meses</p>
            </div>

            <div>
                <label for="fecha_acta" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha del Acta <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_acta" id="fecha_acta" value="{{ old('fecha_acta', date('Y-m-d')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_acta') border-red-500 @enderror">
                @error('fecha_acta')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input type="hidden" name="quorum" value="0">
                    <input type="checkbox" name="quorum" id="quorum" value="1" 
                        {{ old('quorum', false) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="quorum" class="ml-2 block text-sm text-gray-900">
                        Hubo quorum en la reunión
                    </label>
                </div>
                @error('quorum')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Contenido del Acta -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Contenido del Acta</h2>
        
        <div>
            <label for="contenido" class="block text-sm font-medium text-gray-700 mb-1">
                Contenido <span class="text-red-500">*</span>
            </label>
            <textarea name="contenido" id="contenido" rows="15"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contenido') border-red-500 @enderror"
                placeholder="Escriba el contenido del acta aquí...">{{ old('contenido') }}</textarea>
            @error('contenido')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Decisiones -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Decisiones del Acta</h2>
            <button type="button" onclick="agregarDecision()" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>
                Agregar Decisión
            </button>
        </div>
        
        <div id="decisiones-container" class="space-y-4">
            <!-- Las decisiones se agregarán aquí dinámicamente -->
        </div>
    </div>

    <!-- Archivos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
        
        <div>
            <label for="archivos" class="block text-sm font-medium text-gray-700 mb-1">
                Archivos (máx. 10MB por archivo)
            </label>
            <input type="file" name="archivos[]" id="archivos" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('archivos') border-red-500 @enderror">
            @error('archivos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('archivos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Formatos permitidos: PDF, Word, Excel, Imágenes (JPG, PNG)</p>
        </div>

        <div id="archivos-preview" class="mt-4 space-y-2"></div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.consejo-actas.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" id="btnCrearActa" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Crear Acta
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
    const formActa = document.getElementById('formActa');
    const btnCrearActa = document.getElementById('btnCrearActa');
    
    if (formActa) {
        // Agregar listener al botón también
        if (btnCrearActa) {
            btnCrearActa.addEventListener('click', function(e) {
                console.log('Botón clickeado');
                // Sincronizar TinyMCE
                try {
                    const editor = tinymce.get('contenido');
                    if (editor) {
                        editor.save();
                        console.log('TinyMCE sincronizado');
                    }
                } catch (error) {
                    console.error('Error al sincronizar TinyMCE:', error);
                }
            });
        }
        
        formActa.addEventListener('submit', function(e) {
            console.log('Formulario enviándose...');
            
            // Sincronizar TinyMCE con el textarea
            try {
                const editor = tinymce.get('contenido');
                if (editor) {
                    editor.save();
                    console.log('TinyMCE sincronizado en submit');
                }
            } catch (error) {
                console.error('Error al sincronizar TinyMCE:', error);
            }
            
            // Validar contenido manualmente
            const contenido = document.getElementById('contenido').value;
            const contenidoLimpio = contenido.replace(/<[^>]*>/g, '').trim();
            if (!contenidoLimpio || contenidoLimpio === '') {
                e.preventDefault();
                alert('Por favor, ingrese el contenido del acta.');
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
            
            // Validar reunión
            const reunionId = document.getElementById('reunion_id').value;
            if (!reunionId) {
                e.preventDefault();
                alert('Por favor, seleccione una reunión.');
                document.getElementById('reunion_id').focus();
                return false;
            }
            
            console.log('Validaciones pasadas, enviando formulario...');
            
            // Reindexar decisiones antes de enviar el formulario
            const decisiones = document.querySelectorAll('[id^="decision-"]');
            decisiones.forEach((decision, index) => {
                const descripcionInput = decision.querySelector('textarea[name*="[descripcion]"]');
                const responsableInput = decision.querySelector('input[name*="[responsable]"]');
                const fechaInput = decision.querySelector('input[name*="[fecha_compromiso]"]');
                
                if (descripcionInput) {
                    descripcionInput.name = `decisiones[${index}][descripcion]`;
                }
                if (responsableInput) {
                    responsableInput.name = `decisiones[${index}][responsable]`;
                }
                if (fechaInput) {
                    fechaInput.name = `decisiones[${index}][fecha_compromiso]`;
                }
            });
        });
    }

    // Gestión de Decisiones
    let contadorDecisiones = 0;

    window.agregarDecision = function() {
        contadorDecisiones++;
        const container = document.getElementById('decisiones-container');
        const decisionDiv = document.createElement('div');
        decisionDiv.className = 'border border-gray-300 rounded-md p-4';
        decisionDiv.id = `decision-${contadorDecisiones}`;
        
        decisionDiv.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm font-medium text-gray-700">Decisión ${contadorDecisiones}</span>
                <button type="button" onclick="eliminarDecision(${contadorDecisiones})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción <span class="text-red-500">*</span>
                    </label>
                    <textarea name="decisiones[${contadorDecisiones}][descripcion]" required rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Describa la decisión tomada..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Responsable
                    </label>
                    <input type="text" name="decisiones[${contadorDecisiones}][responsable]"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Nombre del responsable">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Compromiso
                    </label>
                    <input type="date" name="decisiones[${contadorDecisiones}][fecha_compromiso]"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        `;
        
        container.appendChild(decisionDiv);
        actualizarNumeracionDecisiones();
    };

    window.eliminarDecision = function(id) {
        const decision = document.getElementById(`decision-${id}`);
        if (decision) {
            decision.remove();
            actualizarNumeracionDecisiones();
        }
    };

    function actualizarNumeracionDecisiones() {
        const decisiones = document.querySelectorAll('[id^="decision-"]');
        decisiones.forEach((decision, index) => {
            const numeroSpan = decision.querySelector('.text-sm.font-medium');
            if (numeroSpan) {
                numeroSpan.textContent = `Decisión ${index + 1}`;
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
