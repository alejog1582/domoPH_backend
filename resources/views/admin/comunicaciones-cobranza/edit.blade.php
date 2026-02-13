@extends('admin.layouts.app')

@section('title', 'Editar Comunicación de Cobranza - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Comunicación de Cobranza</h1>
            <p class="mt-2 text-sm text-gray-600">Modifique la configuración de la comunicación</p>
        </div>
        <a href="{{ route('admin.comunicaciones-cobranza.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.comunicaciones-cobranza.update', $comunicacion->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Nombre -->
        <div class="mb-4">
            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $comunicacion->nombre) }}" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                placeholder="Ej: Recordatorio preventivo, Cobranza mora 30 días">
            @error('nombre')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                Descripción
            </label>
            <textarea name="descripcion" id="descripcion" rows="2"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $comunicacion->descripcion) }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Canal -->
            <div>
                <label for="canal" class="block text-sm font-medium text-gray-700 mb-1">
                    Canal de Envío <span class="text-red-500">*</span>
                </label>
                <select name="canal" id="canal" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('canal') border-red-500 @enderror">
                    <option value="">Seleccione...</option>
                    <option value="email" {{ old('canal', $comunicacion->canal) == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="whatsapp" {{ old('canal', $comunicacion->canal) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="ambos" {{ old('canal', $comunicacion->canal) == 'ambos' ? 'selected' : '' }}>Ambos</option>
                </select>
                @error('canal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Día de Envío -->
            <div>
                <label for="dia_envio_mes" class="block text-sm font-medium text-gray-700 mb-1">
                    Día de Envío del Mes <span class="text-red-500">*</span>
                </label>
                <input type="number" name="dia_envio_mes" id="dia_envio_mes" value="{{ old('dia_envio_mes', $comunicacion->dia_envio_mes) }}" required
                    min="1" max="31"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dia_envio_mes') border-red-500 @enderror"
                    placeholder="1-31">
                <p class="mt-1 text-xs text-gray-500">Día del mes en que se ejecutará el envío automático</p>
                @error('dia_envio_mes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Rango de Mora -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rango de Mora</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="dias_mora_desde" class="block text-xs text-gray-600 mb-1">
                        Días de Mora Desde <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="dias_mora_desde" id="dias_mora_desde" value="{{ old('dias_mora_desde', $comunicacion->dias_mora_desde) }}" required
                        min="0"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dias_mora_desde') border-red-500 @enderror"
                        placeholder="0 = preventivo">
                    <p class="mt-1 text-xs text-gray-500">0 = Sin mora (preventivo)</p>
                    @error('dias_mora_desde')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="dias_mora_hasta" class="block text-xs text-gray-600 mb-1">
                        Días de Mora Hasta
                    </label>
                    <input type="number" name="dias_mora_hasta" id="dias_mora_hasta" value="{{ old('dias_mora_hasta', $comunicacion->dias_mora_hasta) }}"
                        min="0"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dias_mora_hasta') border-red-500 @enderror"
                        placeholder="Dejar vacío = sin límite">
                    <p class="mt-1 text-xs text-gray-500">Dejar vacío para sin límite superior</p>
                    @error('dias_mora_hasta')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Asunto (solo para email) -->
        <div class="mb-4" id="asunto-container">
            <label for="asunto" class="block text-sm font-medium text-gray-700 mb-1">
                Asunto del Email
            </label>
            <input type="text" name="asunto" id="asunto" value="{{ old('asunto', $comunicacion->asunto) }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('asunto') border-red-500 @enderror"
                placeholder="Asunto del correo electrónico">
            @error('asunto')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Mensaje Email -->
        <div class="mb-4" id="mensaje-email-container">
            <label for="mensaje_email" class="block text-sm font-medium text-gray-700 mb-1">
                Mensaje para Email
            </label>
            <p class="text-xs text-gray-500 mb-2">
                Variables disponibles: <code>{nombre_residente}</code>, <code>{unidad}</code>, <code>{saldo}</code>, <code>{fecha_vencimiento}</code>, <code>{copropiedad}</code>
            </p>
            <textarea name="mensaje_email" id="mensaje_email" rows="10"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('mensaje_email') border-red-500 @enderror">{{ old('mensaje_email', $comunicacion->mensaje_email) }}</textarea>
            @error('mensaje_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Mensaje WhatsApp -->
        <div class="mb-4" id="mensaje-whatsapp-container">
            <label for="mensaje_whatsapp" class="block text-sm font-medium text-gray-700 mb-1">
                Mensaje para WhatsApp
            </label>
            <p class="text-xs text-gray-500 mb-2">
                Variables disponibles: <code>{nombre_residente}</code>, <code>{unidad}</code>, <code>{saldo}</code>, <code>{fecha_vencimiento}</code>, <code>{copropiedad}</code>
            </p>
            <textarea name="mensaje_whatsapp" id="mensaje_whatsapp" rows="10"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('mensaje_whatsapp') border-red-500 @enderror">{{ old('mensaje_whatsapp', $comunicacion->mensaje_whatsapp) }}</textarea>
            @error('mensaje_whatsapp')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Activo -->
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $comunicacion->activo) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="activo" class="ml-2 block text-sm text-gray-700">
                    Comunicación activa
                </label>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.comunicaciones-cobranza.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Actualizar Comunicación
            </button>
        </div>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/7x88fqj9jt1cd4s1iw33req3fx7k819a43eoi007vuoradp5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de TinyMCE para mensaje_email
    tinymce.init({
        selector: '#mensaje_email',
        height: 300,
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

    // Configuración de TinyMCE para mensaje_whatsapp
    tinymce.init({
        selector: '#mensaje_whatsapp',
        height: 300,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
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

    // Mostrar/ocultar campos según el canal seleccionado
    function toggleCampos() {
        const canal = document.getElementById('canal').value;
        const asuntoContainer = document.getElementById('asunto-container');
        const emailContainer = document.getElementById('mensaje-email-container');
        const whatsappContainer = document.getElementById('mensaje-whatsapp-container');

        if (canal === 'email') {
            asuntoContainer.style.display = 'block';
            emailContainer.style.display = 'block';
            whatsappContainer.style.display = 'none';
        } else if (canal === 'whatsapp') {
            asuntoContainer.style.display = 'none';
            emailContainer.style.display = 'none';
            whatsappContainer.style.display = 'block';
        } else if (canal === 'ambos') {
            asuntoContainer.style.display = 'block';
            emailContainer.style.display = 'block';
            whatsappContainer.style.display = 'block';
        } else {
            asuntoContainer.style.display = 'none';
            emailContainer.style.display = 'none';
            whatsappContainer.style.display = 'none';
        }
    }

    document.getElementById('canal').addEventListener('change', toggleCampos);
    toggleCampos(); // Ejecutar al cargar la página
});
</script>
@endsection
