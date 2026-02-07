@extends('admin.layouts.app')

@section('title', 'Crear Tarea del Consejo')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.consejo-tareas.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Tareas
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Crear Tarea del Consejo</h1>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<form action="{{ route('admin.consejo-tareas.store') }}" method="POST" class="space-y-6">
    @csrf

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Tarea</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('titulo') border-red-500 @enderror"
                    placeholder="Ej: Revisar presupuesto 2026">
                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-1">
                    Prioridad <span class="text-red-500">*</span>
                </label>
                <select name="prioridad" id="prioridad" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('prioridad') border-red-500 @enderror">
                    <option value="">Seleccione una prioridad</option>
                    <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                    <option value="media" {{ old('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                    <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                </select>
                @error('prioridad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="acta_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Acta (Opcional)
                </label>
                <select name="acta_id" id="acta_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('acta_id') border-red-500 @enderror">
                    <option value="">Sin acta asociada</option>
                    @foreach($actas as $acta)
                    <option value="{{ $acta->id }}" 
                        {{ old('acta_id', $actaId) == $acta->id ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($acta->fecha_acta)->format('d/m/Y') }} - {{ ucfirst($acta->tipo_reunion) }}
                    </option>
                    @endforeach
                </select>
                @error('acta_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Solo se muestran actas de los últimos 3 meses</p>
            </div>

            <div id="decision-container" style="display: {{ $actaId ? 'block' : 'none' }};">
                <label for="decision_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Decisión (Opcional)
                </label>
                <select name="decision_id" id="decision_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('decision_id') border-red-500 @enderror">
                    <option value="">Sin decisión asociada</option>
                    @foreach($decisiones as $decision)
                    <option value="{{ $decision->id }}" 
                        {{ old('decision_id', $decisionId) == $decision->id ? 'selected' : '' }}>
                        {{ Str::limit($decision->descripcion, 50) }}
                    </option>
                    @endforeach
                </select>
                @error('decision_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="responsable_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Responsable
                </label>
                <select name="responsable_id" id="responsable_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('responsable_id') border-red-500 @enderror">
                    <option value="">Sin responsable asignado</option>
                    @foreach($integrantes as $integrante)
                    <option value="{{ $integrante->id }}" {{ old('responsable_id') == $integrante->id ? 'selected' : '' }}>
                        {{ $integrante->nombre }} ({{ ucfirst($integrante->cargo) }})
                    </option>
                    @endforeach
                </select>
                @error('responsable_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Inicio
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_inicio') border-red-500 @enderror">
                @error('fecha_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Vencimiento
                </label>
                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_vencimiento') border-red-500 @enderror">
                @error('fecha_vencimiento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción <span class="text-red-500">*</span>
                </label>
                <textarea name="descripcion" id="descripcion" rows="6" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror"
                    placeholder="Describa la tarea en detalle...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.consejo-tareas.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i>
            Crear Tarea
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const actaSelect = document.getElementById('acta_id');
    const decisionContainer = document.getElementById('decision-container');
    const decisionSelect = document.getElementById('decision_id');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');

    // Cargar decisiones cuando se selecciona un acta
    actaSelect.addEventListener('change', function() {
        const actaId = this.value;
        
        if (actaId) {
            // Mostrar contenedor de decisiones
            decisionContainer.style.display = 'block';
            
            // Cargar decisiones del acta seleccionado
            fetch(`/admin/consejo-tareas/get-decisiones?acta_id=${actaId}`)
                .then(response => response.json())
                .then(data => {
                    decisionSelect.innerHTML = '<option value="">Sin decisión asociada</option>';
                    data.decisiones.forEach(function(decision) {
                        const option = document.createElement('option');
                        option.value = decision.id;
                        option.textContent = decision.descripcion.substring(0, 50) + (decision.descripcion.length > 50 ? '...' : '');
                        decisionSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar decisiones:', error);
                });
        } else {
            // Ocultar contenedor de decisiones
            decisionContainer.style.display = 'none';
            decisionSelect.innerHTML = '<option value="">Sin decisión asociada</option>';
        }
    });

    // Validar que fecha_vencimiento sea >= fecha_inicio
    fechaInicio.addEventListener('change', function() {
        if (fechaVencimiento.value && this.value > fechaVencimiento.value) {
            fechaVencimiento.value = this.value;
        }
        fechaVencimiento.min = this.value;
    });

    fechaVencimiento.addEventListener('change', function() {
        if (fechaInicio.value && this.value < fechaInicio.value) {
            alert('La fecha de vencimiento debe ser mayor o igual a la fecha de inicio.');
            this.value = fechaInicio.value;
        }
    });
});
</script>
@endsection
