@extends('admin.layouts.app')

@section('title', 'Crear Zona Común - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Zona Común</h1>
            <p class="mt-2 text-sm text-gray-600">Registra una nueva zona común</p>
        </div>
        <a href="{{ route('admin.zonas-sociales.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.zonas-sociales.store') }}" method="POST" enctype="multipart/form-data" id="formZonaSocial">
        @csrf

        <!-- Información Básica -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h3>
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
                        value="{{ old('nombre') }}" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror"
                        placeholder="Ej: Salón Social Torre 4"
                    >
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-2">
                        Ubicación
                    </label>
                    <input 
                        type="text" 
                        id="ubicacion" 
                        name="ubicacion" 
                        value="{{ old('ubicacion') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('ubicacion') border-red-500 @enderror"
                        placeholder="Ej: Torre 1 - Piso 1"
                    >
                    @error('ubicacion')
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
                        placeholder="Descripción detallada de la zona común"
                    >{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacidad Máxima -->
                <div>
                    <label for="capacidad_maxima" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacidad Máxima <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="capacidad_maxima" 
                        name="capacidad_maxima" 
                        value="{{ old('capacidad_maxima', 50) }}" 
                        required
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('capacidad_maxima') border-red-500 @enderror"
                    >
                    @error('capacidad_maxima')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Máx Invitados por Reserva -->
                <div>
                    <label for="max_invitados_por_reserva" class="block text-sm font-medium text-gray-700 mb-2">
                        Máx. Invitados por Reserva
                    </label>
                    <input 
                        type="number" 
                        id="max_invitados_por_reserva" 
                        name="max_invitados_por_reserva" 
                        value="{{ old('max_invitados_por_reserva') }}" 
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('max_invitados_por_reserva') border-red-500 @enderror"
                    >
                    @error('max_invitados_por_reserva')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tiempo Mínimo Uso (horas) -->
                <div>
                    <label for="tiempo_minimo_uso_horas" class="block text-sm font-medium text-gray-700 mb-2">
                        Tiempo Mínimo Uso (horas) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="tiempo_minimo_uso_horas" 
                        name="tiempo_minimo_uso_horas" 
                        value="{{ old('tiempo_minimo_uso_horas', 1) }}" 
                        required
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tiempo_minimo_uso_horas') border-red-500 @enderror"
                    >
                    @error('tiempo_minimo_uso_horas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tiempo Máximo Uso (horas) -->
                <div>
                    <label for="tiempo_maximo_uso_horas" class="block text-sm font-medium text-gray-700 mb-2">
                        Tiempo Máximo Uso (horas) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="tiempo_maximo_uso_horas" 
                        name="tiempo_maximo_uso_horas" 
                        value="{{ old('tiempo_maximo_uso_horas', 14) }}" 
                        required
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tiempo_maximo_uso_horas') border-red-500 @enderror"
                    >
                    @error('tiempo_maximo_uso_horas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reservas Simultáneas -->
                <div>
                    <label for="reservas_simultaneas" class="block text-sm font-medium text-gray-700 mb-2">
                        Reservas Simultáneas <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="reservas_simultaneas" 
                        name="reservas_simultaneas" 
                        value="{{ old('reservas_simultaneas', 1) }}" 
                        required
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reservas_simultaneas') border-red-500 @enderror"
                    >
                    @error('reservas_simultaneas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor Alquiler -->
                <div>
                    <label for="valor_alquiler" class="block text-sm font-medium text-gray-700 mb-2">
                        Valor Alquiler
                    </label>
                    <input 
                        type="number" 
                        id="valor_alquiler" 
                        name="valor_alquiler" 
                        value="{{ old('valor_alquiler') }}" 
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('valor_alquiler') border-red-500 @enderror"
                    >
                    @error('valor_alquiler')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor Depósito -->
                <div>
                    <label for="valor_deposito" class="block text-sm font-medium text-gray-700 mb-2">
                        Valor Depósito
                    </label>
                    <input 
                        type="number" 
                        id="valor_deposito" 
                        name="valor_deposito" 
                        value="{{ old('valor_deposito') }}" 
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('valor_deposito') border-red-500 @enderror"
                    >
                    @error('valor_deposito')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="estado" 
                        name="estado" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('estado') border-red-500 @enderror"
                    >
                        <option value="activa" {{ old('estado', 'activa') == 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="inactiva" {{ old('estado') == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                        <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    </select>
                    @error('estado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reglamento URL -->
                <div>
                    <label for="reglamento_url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL del Reglamento
                    </label>
                    <input 
                        type="url" 
                        id="reglamento_url" 
                        name="reglamento_url" 
                        value="{{ old('reglamento_url') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reglamento_url') border-red-500 @enderror"
                        placeholder="https://ejemplo.com/reglamento"
                    >
                    @error('reglamento_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Checkboxes -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="requiere_aprobacion" 
                        name="requiere_aprobacion" 
                        value="1"
                        {{ old('requiere_aprobacion') ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="requiere_aprobacion" class="ml-2 block text-sm text-gray-900">
                        Requiere Aprobación
                    </label>
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="permite_reservas_en_mora" 
                        name="permite_reservas_en_mora" 
                        value="1"
                        {{ old('permite_reservas_en_mora') ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="permite_reservas_en_mora" class="ml-2 block text-sm text-gray-900">
                        Permite Reservas en Mora
                    </label>
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="activo" 
                        name="activo" 
                        value="1"
                        {{ old('activo', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="activo" class="ml-2 block text-sm text-gray-900">
                        Activo
                    </label>
                </div>
            </div>
        </div>

        <!-- Horarios -->
        <div class="mb-8 border-t pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Horarios de Disponibilidad</h3>
                <button 
                    type="button" 
                    onclick="agregarHorario()" 
                    class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Agregar Horario
                </button>
            </div>
            <div id="horarios-container">
                <!-- Los horarios se agregarán dinámicamente aquí -->
            </div>
        </div>

        <!-- Imágenes -->
        <div class="mb-8 border-t pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Imágenes</h3>
                <button 
                    type="button" 
                    onclick="agregarImagen()" 
                    class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Agregar Imagen
                </button>
            </div>
            <div id="imagenes-container">
                <!-- Las imágenes se agregarán dinámicamente aquí -->
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
            <a href="{{ route('admin.zonas-sociales.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Guardar Zona Común
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let horarioIndex = 0;
    let imagenIndex = 0;

    const diasSemana = [
        { value: 'lunes', label: 'Lunes' },
        { value: 'martes', label: 'Martes' },
        { value: 'miércoles', label: 'Miércoles' },
        { value: 'jueves', label: 'Jueves' },
        { value: 'viernes', label: 'Viernes' },
        { value: 'sábado', label: 'Sábado' },
        { value: 'domingo', label: 'Domingo' }
    ];

    // Funciones para Horarios
    function agregarHorario(datos = null) {
        const container = document.getElementById('horarios-container');
        const horarioDiv = document.createElement('div');
        horarioDiv.className = 'mb-4 p-4 border border-gray-300 rounded-md bg-gray-50';
        horarioDiv.id = `horario-${horarioIndex}`;
        
        let diasOptions = '';
        diasSemana.forEach(dia => {
            const selected = datos && datos.dia_semana === dia.value ? 'selected' : '';
            diasOptions += `<option value="${dia.value}" ${selected}>${dia.label}</option>`;
        });

        // Asegurar formato correcto de hora (HH:MM) para input type="time"
        let horaInicio = '';
        let horaFin = '';
        if (datos) {
            horaInicio = datos.hora_inicio ? (datos.hora_inicio.length > 5 ? datos.hora_inicio.substring(0, 5) : datos.hora_inicio) : '';
            horaFin = datos.hora_fin ? (datos.hora_fin.length > 5 ? datos.hora_fin.substring(0, 5) : datos.hora_fin) : '';
        }
        const activo = datos && datos.activo !== undefined ? (datos.activo ? 'checked' : '') : 'checked';

        horarioDiv.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-700">Horario ${horarioIndex + 1}</h4>
                <button type="button" onclick="eliminarHorario(${horarioIndex})" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Día de la Semana <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="horarios[${horarioIndex}][dia_semana]" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Seleccionar día</option>
                        ${diasOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hora Inicio <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="time" 
                        name="horarios[${horarioIndex}][hora_inicio]" 
                        value="${horaInicio}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hora Fin <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="time" 
                        name="horarios[${horarioIndex}][hora_fin]" 
                        value="${horaFin}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>
            <div class="mt-3">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="horarios[${horarioIndex}][activo]" 
                        value="1"
                        ${activo}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <span class="ml-2 text-sm text-gray-900">Activo</span>
                </label>
            </div>
        `;
        
        container.appendChild(horarioDiv);
        horarioIndex++;
    }

    function eliminarHorario(index) {
        const horarioDiv = document.getElementById(`horario-${index}`);
        if (horarioDiv) {
            horarioDiv.remove();
        }
    }

    // Funciones para Imágenes
    function agregarImagen() {
        const container = document.getElementById('imagenes-container');
        const imagenDiv = document.createElement('div');
        imagenDiv.className = 'mb-4 p-4 border border-gray-300 rounded-md bg-gray-50';
        imagenDiv.id = `imagen-${imagenIndex}`;
        
        imagenDiv.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-700">Imagen ${imagenIndex + 1}</h4>
                <button type="button" onclick="eliminarImagen(${imagenIndex})" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Seleccionar Imagen
                </label>
                <input 
                    type="file" 
                    name="imagenes[]" 
                    accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    onchange="previewImagen(this, ${imagenIndex})"
                >
                <div id="preview-imagen-${imagenIndex}" class="mt-2"></div>
            </div>
        `;
        
        container.appendChild(imagenDiv);
        imagenIndex++;
    }

    function eliminarImagen(index) {
        const imagenDiv = document.getElementById(`imagen-${index}`);
        if (imagenDiv) {
            imagenDiv.remove();
        }
    }

    function previewImagen(input, index) {
        const preview = document.getElementById(`preview-imagen-${index}`);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="max-w-xs h-32 object-cover rounded-md border border-gray-300">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Datos anteriores en caso de error de validación
    const horariosAnteriores = @json(old('horarios', []));

    // Inicializar con datos anteriores o valores por defecto
    document.addEventListener('DOMContentLoaded', function() {
        // Restaurar horarios anteriores si existen
        if (horariosAnteriores && horariosAnteriores.length > 0) {
            horariosAnteriores.forEach(function(horario) {
                if (horario && horario.dia_semana && horario.hora_inicio && horario.hora_fin) {
                    // Asegurar formato correcto de hora (HH:MM)
                    const horaInicio = horario.hora_inicio && horario.hora_inicio.length > 5 ? horario.hora_inicio.substring(0, 5) : (horario.hora_inicio || '');
                    const horaFin = horario.hora_fin && horario.hora_fin.length > 5 ? horario.hora_fin.substring(0, 5) : (horario.hora_fin || '');
                    agregarHorario({
                        dia_semana: horario.dia_semana,
                        hora_inicio: horaInicio,
                        hora_fin: horaFin,
                        activo: horario.activo !== undefined ? horario.activo : true
                    });
                }
            });
        } else {
            // Si no hay datos anteriores, agregar uno por defecto
            agregarHorario();
        }

        // Para imágenes, siempre agregar un campo nuevo ya que los archivos no se pueden restaurar
        // Si había errores, mostrar mensaje informativo
        @if($errors->has('imagenes.*') || $errors->has('imagenes.0') || $errors->has('imagenes'))
            // Agregar mensaje informativo sobre las imágenes
            const imagenesContainer = document.getElementById('imagenes-container');
            const mensajeDiv = document.createElement('div');
            mensajeDiv.className = 'mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md';
            mensajeDiv.innerHTML = `
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Hubo un error con las imágenes. Por favor, vuelve a seleccionar las imágenes.
                </p>
            `;
            imagenesContainer.insertBefore(mensajeDiv, imagenesContainer.firstChild);
        @endif
        
        agregarImagen();
    });
</script>
@endpush
@endsection
