@extends('admin.layouts.app')

@section('title', 'Editar Reunión del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-reuniones.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Reuniones
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Editar Reunión del Consejo</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.consejo-reuniones.update', $reunion->id) }}" method="POST" class="space-y-6" id="formReunion">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Reunión</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $reunion->titulo) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="tipo_reunion" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Reunión <span class="text-red-500">*</span>
                </label>
                <select name="tipo_reunion" id="tipo_reunion" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="ordinaria" {{ old('tipo_reunion', $reunion->tipo_reunion) == 'ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                    <option value="extraordinaria" {{ old('tipo_reunion', $reunion->tipo_reunion) == 'extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                </select>
            </div>

            <div>
                <label for="modalidad" class="block text-sm font-medium text-gray-700 mb-1">
                    Modalidad <span class="text-red-500">*</span>
                </label>
                <select name="modalidad" id="modalidad" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="presencial" {{ old('modalidad', $reunion->modalidad) == 'presencial' ? 'selected' : '' }}>Presencial</option>
                    <option value="virtual" {{ old('modalidad', $reunion->modalidad) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                    <option value="mixta" {{ old('modalidad', $reunion->modalidad) == 'mixta' ? 'selected' : '' }}>Mixta</option>
                </select>
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y Hora de Inicio <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" 
                    value="{{ old('fecha_inicio', \Carbon\Carbon::parse($reunion->fecha_inicio)->format('Y-m-d\TH:i')) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y Hora de Fin
                </label>
                <input type="datetime-local" name="fecha_fin" id="fecha_fin" 
                    value="{{ old('fecha_fin', $reunion->fecha_fin ? \Carbon\Carbon::parse($reunion->fecha_fin)->format('Y-m-d\TH:i') : '') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div id="campo_lugar" style="display: {{ in_array($reunion->modalidad, ['presencial', 'mixta']) ? 'block' : 'none' }};">
                <label for="lugar" class="block text-sm font-medium text-gray-700 mb-1">
                    Lugar
                </label>
                <input type="text" name="lugar" id="lugar" value="{{ old('lugar', $reunion->lugar) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ej: Salón de eventos, Torre 1">
            </div>

            <div id="campo_enlace" style="display: {{ in_array($reunion->modalidad, ['virtual', 'mixta']) ? 'block' : 'none' }};">
                <label for="enlace_virtual" class="block text-sm font-medium text-gray-700 mb-1">
                    Enlace Virtual
                </label>
                <input type="url" name="enlace_virtual" id="enlace_virtual" value="{{ old('enlace_virtual', $reunion->enlace_virtual) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="https://meet.google.com/...">
            </div>
        </div>

        <div class="mt-6">
            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                Observaciones
            </label>
            <textarea name="observaciones" id="observaciones" rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('observaciones', $reunion->observaciones) }}</textarea>
        </div>
    </div>

    <!-- Agenda -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Agenda de la Reunión</h2>
            <button type="button" onclick="agregarPuntoAgenda()" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>
                Agregar Punto
            </button>
        </div>
        
        <div id="agenda-container" class="space-y-4">
            @foreach($agenda as $index => $punto)
            <div class="border border-gray-300 rounded-md p-4" id="punto-agenda-{{ $index }}">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">Punto {{ $index + 1 }}</span>
                    <button type="button" onclick="eliminarPuntoAgenda({{ $index }})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tema <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="agenda[{{ $index }}][tema]" value="{{ $punto->tema }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: Aprobación de presupuesto">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Responsable
                        </label>
                        <input type="text" name="agenda[{{ $index }}][responsable]" value="{{ $punto->responsable }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Nombre del responsable">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Integrantes -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Integrantes que Asistirán</h2>
        
        @if($integrantes->count() > 0)
        <div class="space-y-3">
            @foreach($integrantes as $integrante)
            <div class="flex items-center">
                <input type="checkbox" name="integrantes[]" id="integrante_{{ $integrante->id }}" value="{{ $integrante->id }}"
                    {{ in_array($integrante->id, $asistencias) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="integrante_{{ $integrante->id }}" class="ml-2 block text-sm text-gray-900">
                    {{ $integrante->nombre }} 
                    <span class="text-gray-500">({{ ucfirst($integrante->cargo) }})</span>
                    @if($integrante->es_presidente ?? false)
                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-crown mr-1"></i>Presidente
                    </span>
                    @endif
                </label>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No hay integrantes activos en el consejo.</p>
        @endif
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.consejo-reuniones.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Actualizar Reunión
        </button>
    </div>
</form>

<script>
let contadorAgenda = {{ $agenda->count() }};
let puntosAgenda = [];

// Inicializar puntos existentes
@foreach($agenda as $index => $punto)
puntosAgenda.push({{ $index }});
@endforeach

function agregarPuntoAgenda() {
    const container = document.getElementById('agenda-container');
    const puntoDiv = document.createElement('div');
    puntoDiv.className = 'border border-gray-300 rounded-md p-4';
    puntoDiv.id = `punto-agenda-${contadorAgenda}`;
    puntoDiv.dataset.index = contadorAgenda;
    
    puntoDiv.innerHTML = `
        <div class="flex items-start justify-between mb-3">
            <span class="text-sm font-medium text-gray-700">Punto ${puntosAgenda.length + 1}</span>
            <button type="button" onclick="eliminarPuntoAgenda(${contadorAgenda})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tema <span class="text-red-500">*</span>
                </label>
                <input type="text" name="agenda[${contadorAgenda}][tema]" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ej: Aprobación de presupuesto">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Responsable
                </label>
                <input type="text" name="agenda[${contadorAgenda}][responsable]"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Nombre del responsable">
            </div>
        </div>
    `;
    
    container.appendChild(puntoDiv);
    puntosAgenda.push(contadorAgenda);
    contadorAgenda++;
    actualizarNumeracionAgenda();
}

function eliminarPuntoAgenda(id) {
    const punto = document.getElementById(`punto-agenda-${id}`);
    if (punto) {
        punto.remove();
        puntosAgenda = puntosAgenda.filter(p => p !== id);
        actualizarNumeracionAgenda();
    }
}

function actualizarNumeracionAgenda() {
    const puntos = document.querySelectorAll('[id^="punto-agenda-"]');
    puntos.forEach((punto, index) => {
        const numeroSpan = punto.querySelector('.text-sm.font-medium');
        if (numeroSpan) {
            numeroSpan.textContent = `Punto ${index + 1}`;
        }
    });
}

// Reindexar agenda antes de enviar el formulario
document.getElementById('formReunion').addEventListener('submit', function(e) {
    const puntos = document.querySelectorAll('[id^="punto-agenda-"]');
    puntos.forEach((punto, index) => {
        const temaInput = punto.querySelector('input[name*="[tema]"]');
        const responsableInput = punto.querySelector('input[name*="[responsable]"]');
        
        if (temaInput) {
            temaInput.name = `agenda[${index}][tema]`;
        }
        if (responsableInput) {
            responsableInput.name = `agenda[${index}][responsable]`;
        }
    });
});

// Mostrar/ocultar campos según modalidad
document.getElementById('modalidad').addEventListener('change', function() {
    const modalidad = this.value;
    const campoLugar = document.getElementById('campo_lugar');
    const campoEnlace = document.getElementById('campo_enlace');
    
    if (modalidad === 'presencial' || modalidad === 'mixta') {
        campoLugar.style.display = 'block';
    } else {
        campoLugar.style.display = 'none';
    }
    
    if (modalidad === 'virtual' || modalidad === 'mixta') {
        campoEnlace.style.display = 'block';
    } else {
        campoEnlace.style.display = 'none';
    }
});
</script>
@endsection
