@extends('admin.layouts.app')

@section('title', 'Editar Asamblea')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.asambleas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Asambleas
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Editar Asamblea</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.asambleas.update', $asamblea->id) }}" method="POST" class="space-y-6" enctype="multipart/form-data" id="formAsamblea">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Asamblea</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $asamblea->titulo) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror"
                    placeholder="Ej: Asamblea General Ordinaria 2026">
                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción
                </label>
                <textarea name="descripcion" id="descripcion" rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror"
                    placeholder="Descripción de la asamblea...">{{ old('descripcion', $asamblea->descripcion) }}</textarea>
                @error('descripcion')
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
                    <option value="ordinaria" {{ old('tipo', $asamblea->tipo) == 'ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                    <option value="extraordinaria" {{ old('tipo', $asamblea->tipo) == 'extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="modalidad" class="block text-sm font-medium text-gray-700 mb-1">
                    Modalidad <span class="text-red-500">*</span>
                </label>
                <select name="modalidad" id="modalidad" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('modalidad') border-red-500 @enderror">
                    <option value="">Seleccione una modalidad</option>
                    <option value="presencial" {{ old('modalidad', $asamblea->modalidad) == 'presencial' ? 'selected' : '' }}>Presencial</option>
                    <option value="virtual" {{ old('modalidad', $asamblea->modalidad) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                    <option value="mixta" {{ old('modalidad', $asamblea->modalidad) == 'mixta' ? 'selected' : '' }}>Mixta</option>
                </select>
                @error('modalidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y Hora de Inicio <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" 
                    value="{{ old('fecha_inicio', \Carbon\Carbon::parse($asamblea->fecha_inicio)->format('Y-m-d\TH:i')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio') border-red-500 @enderror">
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y Hora de Fin <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="fecha_fin" id="fecha_fin" 
                    value="{{ old('fecha_fin', \Carbon\Carbon::parse($asamblea->fecha_fin)->format('Y-m-d\TH:i')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_fin') border-red-500 @enderror">
                @error('fecha_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="quorum_minimo" class="block text-sm font-medium text-gray-700 mb-1">
                    Quorum Mínimo (%) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="quorum_minimo" id="quorum_minimo" 
                    value="{{ old('quorum_minimo', $asamblea->quorum_minimo) }}" 
                    min="0" max="100" step="0.01" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('quorum_minimo') border-red-500 @enderror">
                @error('quorum_minimo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="transmision-container" style="display: {{ in_array($asamblea->modalidad, ['virtual', 'mixta']) ? 'block' : 'none' }};">
                <label for="url_transmision" class="block text-sm font-medium text-gray-700 mb-1">
                    URL de Transmisión
                </label>
                <input type="url" name="url_transmision" id="url_transmision" value="{{ old('url_transmision', $asamblea->url_transmision) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('url_transmision') border-red-500 @enderror"
                    placeholder="https://...">
                @error('url_transmision')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="proveedor-container" style="display: {{ in_array($asamblea->modalidad, ['virtual', 'mixta']) ? 'block' : 'none' }};">
                <label for="proveedor_transmision" class="block text-sm font-medium text-gray-700 mb-1">
                    Proveedor de Transmisión
                </label>
                <select name="proveedor_transmision" id="proveedor_transmision"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('proveedor_transmision') border-red-500 @enderror">
                    <option value="">Seleccione un proveedor</option>
                    <option value="daily" {{ old('proveedor_transmision', $asamblea->proveedor_transmision) == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="livekit" {{ old('proveedor_transmision', $asamblea->proveedor_transmision) == 'livekit' ? 'selected' : '' }}>LiveKit</option>
                    <option value="agora" {{ old('proveedor_transmision', $asamblea->proveedor_transmision) == 'agora' ? 'selected' : '' }}>Agora</option>
                    <option value="twilio" {{ old('proveedor_transmision', $asamblea->proveedor_transmision) == 'twilio' ? 'selected' : '' }}>Twilio</option>
                </select>
                @error('proveedor_transmision')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="token-container" style="display: {{ in_array($asamblea->modalidad, ['virtual', 'mixta']) ? 'block' : 'none' }};">
                <label for="token_transmision" class="block text-sm font-medium text-gray-700 mb-1">
                    Token de Transmisión
                </label>
                <textarea name="token_transmision" id="token_transmision" rows="2"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('token_transmision') border-red-500 @enderror"
                    placeholder="Token de acceso...">{{ old('token_transmision', $asamblea->token_transmision) }}</textarea>
                @error('token_transmision')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Archivos Existentes -->
    @if($documentos->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Archivos Existentes</h2>
        
        <div class="space-y-3">
            @foreach($documentos as $documento)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" name="archivos_eliminar[]" value="{{ $documento->id }}" id="archivo_{{ $documento->id }}"
                        class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="archivo_{{ $documento->id }}" class="flex-1 cursor-pointer">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-file text-gray-400"></i>
                            <span class="text-sm font-medium text-gray-900">{{ $documento->nombre }}</span>
                            <span class="text-xs text-gray-500">
                                ({{ ucfirst(str_replace('_', ' ', $documento->tipo)) }})
                            </span>
                        </div>
                    </label>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ $documento->archivo_url }}" target="_blank" class="text-blue-600 hover:text-blue-900 text-sm">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Ver
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <p class="mt-3 text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>
            Marque los archivos que desea eliminar.
        </p>
    </div>
    @endif

    <!-- Nuevos Archivos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Agregar Nuevos Archivos</h2>
        
        <div id="archivos-container">
            <div class="archivo-item border border-gray-300 rounded-md p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Archivo
                        </label>
                        <input type="file" name="archivos[]" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo
                        </label>
                        <select name="tipos_archivo[]"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="orden_dia">Orden del Día</option>
                            <option value="acta">Acta</option>
                            <option value="presupuesto">Presupuesto</option>
                            <option value="soporte">Soporte</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Visible Para
                        </label>
                        <select name="visible_para_archivos[]"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="todos">Todos</option>
                            <option value="propietarios">Propietarios</option>
                            <option value="administracion">Administración</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" onclick="agregarArchivo()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>
            Agregar Archivo
        </button>
        <p class="mt-2 text-xs text-gray-500">Tamaño máximo por archivo: 10MB</p>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.asambleas.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Actualizar Asamblea
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalidadSelect = document.getElementById('modalidad');
    const transmisionContainer = document.getElementById('transmision-container');
    const proveedorContainer = document.getElementById('proveedor-container');
    const tokenContainer = document.getElementById('token-container');

    modalidadSelect.addEventListener('change', function() {
        if (this.value === 'virtual' || this.value === 'mixta') {
            transmisionContainer.style.display = 'block';
            proveedorContainer.style.display = 'block';
            tokenContainer.style.display = 'block';
        } else {
            transmisionContainer.style.display = 'none';
            proveedorContainer.style.display = 'none';
            tokenContainer.style.display = 'none';
        }
    });

    // Validar que fecha_fin sea mayor que fecha_inicio
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    fechaInicio.addEventListener('change', function() {
        fechaFin.min = this.value;
        if (fechaFin.value && fechaFin.value <= this.value) {
            fechaFin.value = '';
        }
    });

    fechaFin.addEventListener('change', function() {
        if (fechaInicio.value && this.value <= fechaInicio.value) {
            alert('La fecha de fin debe ser mayor que la fecha de inicio.');
            this.value = '';
        }
    });
});

let contadorArchivos = 1;

function agregarArchivo() {
    const container = document.getElementById('archivos-container');
    const nuevoArchivo = document.createElement('div');
    nuevoArchivo.className = 'archivo-item border border-gray-300 rounded-md p-4 mb-4';
    nuevoArchivo.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Archivo ${contadorArchivos + 1}</span>
            <button type="button" onclick="eliminarArchivo(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo
                </label>
                <input type="file" name="archivos[]" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo
                </label>
                <select name="tipos_archivo[]"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="orden_dia">Orden del Día</option>
                    <option value="acta">Acta</option>
                    <option value="presupuesto">Presupuesto</option>
                    <option value="soporte">Soporte</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Visible Para
                </label>
                <select name="visible_para_archivos[]"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="todos">Todos</option>
                    <option value="propietarios">Propietarios</option>
                    <option value="administracion">Administración</option>
                </select>
            </div>
        </div>
    `;
    container.appendChild(nuevoArchivo);
    contadorArchivos++;
}

function eliminarArchivo(button) {
    button.closest('.archivo-item').remove();
}
</script>
@endsection
