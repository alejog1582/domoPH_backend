@extends('admin.layouts.app')

@section('title', 'Editar Parqueadero - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Parqueadero</h1>
            <p class="mt-2 text-sm text-gray-600">Modifica la información del parqueadero</p>
        </div>
        <a href="{{ route('admin.parqueaderos.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.parqueaderos.update', $parqueadero->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Código -->
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                    Código <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="codigo" 
                    name="codigo" 
                    value="{{ old('codigo', $parqueadero->codigo) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                >
                @error('codigo')
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
                    <option value="privado" {{ old('tipo', $parqueadero->tipo) == 'privado' ? 'selected' : '' }}>Privado</option>
                    <option value="comunal" {{ old('tipo', $parqueadero->tipo) == 'comunal' ? 'selected' : '' }}>Comunal</option>
                    <option value="visitantes" {{ old('tipo', $parqueadero->tipo) == 'visitantes' ? 'selected' : '' }}>Visitantes</option>
                </select>
                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Vehículo -->
            <div>
                <label for="tipo_vehiculo" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Vehículo <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo_vehiculo" 
                    name="tipo_vehiculo" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo_vehiculo') border-red-500 @enderror"
                >
                    <option value="">Seleccione un tipo</option>
                    <option value="carro" {{ old('tipo_vehiculo', $parqueadero->tipo_vehiculo) == 'carro' ? 'selected' : '' }}>Carro</option>
                    <option value="moto" {{ old('tipo_vehiculo', $parqueadero->tipo_vehiculo) == 'moto' ? 'selected' : '' }}>Moto</option>
                </select>
                @error('tipo_vehiculo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nivel -->
            <div>
                <label for="nivel" class="block text-sm font-medium text-gray-700 mb-2">Nivel</label>
                <input 
                    type="text" 
                    id="nivel" 
                    name="nivel" 
                    value="{{ old('nivel', $parqueadero->nivel) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nivel') border-red-500 @enderror"
                >
                @error('nivel')
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
                    <option value="">Seleccione un estado</option>
                    <option value="disponible" {{ old('estado', $parqueadero->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="ocupado" {{ old('estado', $parqueadero->estado) == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                    <option value="en_mantenimiento" {{ old('estado', $parqueadero->estado) == 'en_mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                    <option value="inhabilitado" {{ old('estado', $parqueadero->estado) == 'inhabilitado' ? 'selected' : '' }}>Inhabilitado</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Es Cubierto -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="es_cubierto" 
                        value="1"
                        {{ old('es_cubierto', $parqueadero->es_cubierto) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Es Cubierto</span>
                </label>
            </div>

            <!-- Unidad -->
            <div>
                <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-2">Unidad Asignada</label>
                <select 
                    id="unidad_id" 
                    name="unidad_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('unidad_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione una unidad (opcional)</option>
                    @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id }}" {{ old('unidad_id', $parqueadero->unidad_id) == $unidad->id ? 'selected' : '' }}>
                            {{ $unidad->numero }} @if($unidad->torre) - Torre {{ $unidad->torre }} @endif @if($unidad->bloque) - Bloque {{ $unidad->bloque }} @endif
                        </option>
                    @endforeach
                </select>
                @error('unidad_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Residente Responsable -->
            <div>
                <label for="residente_responsable_id" class="block text-sm font-medium text-gray-700 mb-2">Residente Responsable</label>
                <select 
                    id="residente_responsable_id" 
                    name="residente_responsable_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('residente_responsable_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione un residente (opcional)</option>
                    @foreach($residentes as $residente)
                        <option value="{{ $residente->id }}" {{ old('residente_responsable_id', $parqueadero->residente_responsable_id) == $residente->id ? 'selected' : '' }}>
                            {{ $residente->user->nombre ?? 'N/A' }} - {{ $residente->user->documento_identidad ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('residente_responsable_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Asignación -->
            <div>
                <label for="fecha_asignacion" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Asignación</label>
                <input 
                    type="date" 
                    id="fecha_asignacion" 
                    name="fecha_asignacion" 
                    value="{{ old('fecha_asignacion', $parqueadero->fecha_asignacion ? $parqueadero->fecha_asignacion->format('Y-m-d') : '') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_asignacion') border-red-500 @enderror"
                >
                @error('fecha_asignacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Observaciones -->
            <div class="md:col-span-2">
                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                <textarea 
                    id="observaciones" 
                    name="observaciones" 
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('observaciones') border-red-500 @enderror"
                >{{ old('observaciones', $parqueadero->observaciones) }}</textarea>
                @error('observaciones')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.parqueaderos.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Actualizar Parqueadero
            </button>
        </div>
    </form>
</div>
@endsection
