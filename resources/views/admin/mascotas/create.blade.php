@extends('admin.layouts.app')

@section('title', 'Crear Mascota - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Mascota</h1>
            <p class="mt-2 text-sm text-gray-600">Registra una nueva mascota</p>
        </div>
        <a href="{{ route('admin.mascotas.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.mascotas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de la Mascota</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Unidad -->
            <div>
                <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Unidad <span class="text-red-500">*</span>
                </label>
                <select 
                    id="unidad_id" 
                    name="unidad_id" 
                    required
                    onchange="cargarResidentes(this.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('unidad_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione una unidad</option>
                    @foreach($unidades as $unidad)
                        @php
                            $unidadLabel = $unidad->numero;
                            if ($unidad->torre) $unidadLabel .= ' - ' . $unidad->torre;
                            if ($unidad->bloque) $unidadLabel .= ' - ' . $unidad->bloque;
                        @endphp
                        <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }} data-residentes="{{ json_encode($unidad->residentes->map(function($r) { return ['id' => $r->id, 'nombre' => $r->user->nombre ?? 'Sin nombre']; })) }}">
                            {{ $unidadLabel }}
                        </option>
                    @endforeach
                </select>
                @error('unidad_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Residente -->
            <div>
                <label for="residente_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Residente <span class="text-red-500">*</span>
                </label>
                <select 
                    id="residente_id" 
                    name="residente_id" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('residente_id') border-red-500 @enderror"
                >
                    <option value="">Primero seleccione una unidad</option>
                </select>
                @error('residente_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

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
                >
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo -->
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo" 
                    name="tipo" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo') border-red-500 @enderror"
                >
                    <option value="">Seleccione un tipo</option>
                    <option value="perro" {{ old('tipo') == 'perro' ? 'selected' : '' }}>Perro</option>
                    <option value="gato" {{ old('tipo') == 'gato' ? 'selected' : '' }}>Gato</option>
                    <option value="ave" {{ old('tipo') == 'ave' ? 'selected' : '' }}>Ave</option>
                    <option value="reptil" {{ old('tipo') == 'reptil' ? 'selected' : '' }}>Reptil</option>
                    <option value="roedor" {{ old('tipo') == 'roedor' ? 'selected' : '' }}>Roedor</option>
                    <option value="otro" {{ old('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Raza -->
            <div>
                <label for="raza" class="block text-sm font-medium text-gray-700 mb-2">Raza</label>
                <input 
                    type="text" 
                    id="raza" 
                    name="raza" 
                    value="{{ old('raza') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('raza') border-red-500 @enderror"
                >
                @error('raza')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                <input 
                    type="text" 
                    id="color" 
                    name="color" 
                    value="{{ old('color') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('color') border-red-500 @enderror"
                >
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sexo -->
            <div>
                <label for="sexo" class="block text-sm font-medium text-gray-700 mb-2">
                    Sexo <span class="text-red-500">*</span>
                </label>
                <select 
                    id="sexo" 
                    name="sexo" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('sexo') border-red-500 @enderror"
                >
                    <option value="">Seleccione un sexo</option>
                    <option value="macho" {{ old('sexo') == 'macho' ? 'selected' : '' }}>Macho</option>
                    <option value="hembra" {{ old('sexo') == 'hembra' ? 'selected' : '' }}>Hembra</option>
                    <option value="desconocido" {{ old('sexo') == 'desconocido' ? 'selected' : '' }}>Desconocido</option>
                </select>
                @error('sexo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Nacimiento -->
            <div>
                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Nacimiento</label>
                <input 
                    type="date" 
                    id="fecha_nacimiento" 
                    name="fecha_nacimiento" 
                    value="{{ old('fecha_nacimiento') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_nacimiento') border-red-500 @enderror"
                >
                @error('fecha_nacimiento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Edad Aproximada -->
            <div>
                <label for="edad_aproximada" class="block text-sm font-medium text-gray-700 mb-2">Edad Aproximada (meses)</label>
                <input 
                    type="number" 
                    id="edad_aproximada" 
                    name="edad_aproximada" 
                    value="{{ old('edad_aproximada') }}" 
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('edad_aproximada') border-red-500 @enderror"
                >
                @error('edad_aproximada')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Peso -->
            <div>
                <label for="peso_kg" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                <input 
                    type="number" 
                    id="peso_kg" 
                    name="peso_kg" 
                    value="{{ old('peso_kg') }}" 
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('peso_kg') border-red-500 @enderror"
                >
                @error('peso_kg')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tamaño -->
            <div>
                <label for="tamanio" class="block text-sm font-medium text-gray-700 mb-2">Tamaño</label>
                <select 
                    id="tamanio" 
                    name="tamanio" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tamanio') border-red-500 @enderror"
                >
                    <option value="">Seleccione un tamaño</option>
                    <option value="pequeño" {{ old('tamanio') == 'pequeño' ? 'selected' : '' }}>Pequeño</option>
                    <option value="mediano" {{ old('tamanio') == 'mediano' ? 'selected' : '' }}>Mediano</option>
                    <option value="grande" {{ old('tamanio') == 'grande' ? 'selected' : '' }}>Grande</option>
                </select>
                @error('tamanio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Número de Chip -->
            <div>
                <label for="numero_chip" class="block text-sm font-medium text-gray-700 mb-2">Número de Chip</label>
                <input 
                    type="text" 
                    id="numero_chip" 
                    name="numero_chip" 
                    value="{{ old('numero_chip') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('numero_chip') border-red-500 @enderror"
                >
                @error('numero_chip')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado de Salud -->
            <div>
                <label for="estado_salud" class="block text-sm font-medium text-gray-700 mb-2">Estado de Salud</label>
                <select 
                    id="estado_salud" 
                    name="estado_salud" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('estado_salud') border-red-500 @enderror"
                >
                    <option value="">Seleccione un estado</option>
                    <option value="saludable" {{ old('estado_salud') == 'saludable' ? 'selected' : '' }}>Saludable</option>
                    <option value="en_tratamiento" {{ old('estado_salud') == 'en_tratamiento' ? 'selected' : '' }}>En Tratamiento</option>
                    <option value="crónico" {{ old('estado_salud') == 'crónico' ? 'selected' : '' }}>Crónico</option>
                    <option value="desconocido" {{ old('estado_salud') == 'desconocido' ? 'selected' : '' }}>Desconocido</option>
                </select>
                @error('estado_salud')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha Vigencia Vacunas -->
            <div>
                <label for="fecha_vigencia_vacunas" class="block text-sm font-medium text-gray-700 mb-2">Fecha Vigencia Vacunas</label>
                <input 
                    type="date" 
                    id="fecha_vigencia_vacunas" 
                    name="fecha_vigencia_vacunas" 
                    value="{{ old('fecha_vigencia_vacunas') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_vigencia_vacunas') border-red-500 @enderror"
                >
                @error('fecha_vigencia_vacunas')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Vacunado -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="vacunado" 
                        value="1"
                        {{ old('vacunado') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Vacunado</span>
                </label>
            </div>

            <!-- Esterilizado -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="esterilizado" 
                        value="1"
                        {{ old('esterilizado') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Esterilizado</span>
                </label>
            </div>

            <!-- Activo -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="activo" 
                        value="1"
                        {{ old('activo', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                </label>
            </div>

            <!-- Observaciones -->
            <div class="md:col-span-2">
                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                <textarea 
                    id="observaciones" 
                    name="observaciones" 
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('observaciones') border-red-500 @enderror"
                >{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Foto de la Mascota -->
            <div class="md:col-span-2">
                <label for="foto_mascota" class="block text-sm font-medium text-gray-700 mb-2">Foto de la Mascota</label>
                <input 
                    type="file" 
                    id="foto_mascota" 
                    name="foto_mascota" 
                    accept="image/jpeg,image/png,image/jpg,image/gif"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('foto_mascota') border-red-500 @enderror"
                >
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPEG, PNG, JPG, GIF. Tamaño máximo: 5MB</p>
                @error('foto_mascota')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Foto de Vacunación -->
            <div class="md:col-span-2">
                <label for="foto_vacunacion" class="block text-sm font-medium text-gray-700 mb-2">Foto de Vacunación</label>
                <input 
                    type="file" 
                    id="foto_vacunacion" 
                    name="foto_vacunacion" 
                    accept="image/jpeg,image/png,image/jpg,image/gif"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('foto_vacunacion') border-red-500 @enderror"
                >
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPEG, PNG, JPG, GIF. Tamaño máximo: 5MB</p>
                @error('foto_vacunacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.mascotas.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Crear Mascota
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function cargarResidentes(unidadId) {
        const selectUnidad = document.getElementById('unidad_id');
        const selectResidente = document.getElementById('residente_id');
        const optionSeleccionada = selectUnidad.options[selectUnidad.selectedIndex];
        
        // Limpiar opciones actuales
        selectResidente.innerHTML = '<option value="">Seleccione un residente</option>';
        
        if (unidadId && optionSeleccionada.dataset.residentes) {
            try {
                const residentes = JSON.parse(optionSeleccionada.dataset.residentes);
                residentes.forEach(function(residente) {
                    const option = document.createElement('option');
                    option.value = residente.id;
                    option.textContent = residente.nombre;
                    selectResidente.appendChild(option);
                });
            } catch (e) {
                console.error('Error al cargar residentes:', e);
            }
        }
    }

    // Cargar residentes si hay una unidad seleccionada al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const unidadId = document.getElementById('unidad_id').value;
        if (unidadId) {
            cargarResidentes(unidadId);
            const residenteId = '{{ old("residente_id") }}';
            if (residenteId) {
                document.getElementById('residente_id').value = residenteId;
            }
        }
    });
</script>
@endpush
@endsection
