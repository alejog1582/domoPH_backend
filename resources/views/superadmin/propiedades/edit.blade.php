@extends('layouts.app')

@section('title', 'Editar Propiedad - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Editar Propiedad</h1>
    <p class="mt-2 text-sm text-gray-600">Actualiza la información de la copropiedad</p>
</div>

<form action="{{ route('superadmin.propiedades.update', $propiedad->id) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <!-- Información Básica de la Propiedad -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-building mr-2"></i> Información Básica
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre de la Propiedad <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $propiedad->nombre) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nit" class="block text-sm font-medium text-gray-700 mb-2">
                    NIT
                </label>
                <input type="text" name="nit" id="nit" value="{{ old('nit', $propiedad->nit) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nit') border-red-500 @enderror">
                @error('nit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Plan <span class="text-red-500">*</span>
                </label>
                <select name="plan_id" id="plan_id" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('plan_id') border-red-500 @enderror">
                    <option value="">Seleccione un plan</option>
                    @foreach($planes as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $propiedad->plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('plan_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                    Dirección <span class="text-red-500">*</span>
                </label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $propiedad->direccion) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('direccion') border-red-500 @enderror">
                @error('direccion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-2">
                    Ciudad <span class="text-red-500">*</span>
                </label>
                <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad', $propiedad->ciudad) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ciudad') border-red-500 @enderror">
                @error('ciudad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="departamento" class="block text-sm font-medium text-gray-700 mb-2">
                    Departamento <span class="text-red-500">*</span>
                </label>
                <input type="text" name="departamento" id="departamento" value="{{ old('departamento', $propiedad->departamento) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('departamento') border-red-500 @enderror">
                @error('departamento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="codigo_postal" class="block text-sm font-medium text-gray-700 mb-2">
                    Código Postal
                </label>
                <input type="text" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal', $propiedad->codigo_postal) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo_postal') border-red-500 @enderror">
                @error('codigo_postal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                    Teléfono <span class="text-red-500">*</span>
                </label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $propiedad->telefono) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefono') border-red-500 @enderror">
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $propiedad->email) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="total_unidades" class="block text-sm font-medium text-gray-700 mb-2">
                    Total de Unidades <span class="text-red-500">*</span>
                </label>
                <input type="number" name="total_unidades" id="total_unidades" value="{{ old('total_unidades', $propiedad->total_unidades) }}" min="0" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_unidades') border-red-500 @enderror">
                @error('total_unidades')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select name="estado" id="estado" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('estado') border-red-500 @enderror">
                    <option value="activa" {{ old('estado', $propiedad->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="suspendida" {{ old('estado', $propiedad->estado) == 'suspendida' ? 'selected' : '' }}>Suspendida</option>
                    <option value="cancelada" {{ old('estado', $propiedad->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color_primario" class="block text-sm font-medium text-gray-700 mb-2">
                    Color Primario <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="color" name="color_primario" id="color_primario" value="{{ old('color_primario', $propiedad->color_primario ?? '#0066CC') }}" required
                        class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer @error('color_primario') border-red-500 @enderror">
                    <input type="text" name="color_primario" value="{{ old('color_primario', $propiedad->color_primario ?? '#0066CC') }}" 
                        pattern="^#[0-9A-Fa-f]{6}$" placeholder="#0066CC"
                        class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('color_primario') border-red-500 @enderror">
                </div>
                @error('color_primario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color_secundario" class="block text-sm font-medium text-gray-700 mb-2">
                    Color Secundario <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="color" name="color_secundario" id="color_secundario" value="{{ old('color_secundario', $propiedad->color_secundario ?? '#FFFFFF') }}" required
                        class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer @error('color_secundario') border-red-500 @enderror">
                    <input type="text" name="color_secundario" value="{{ old('color_secundario', $propiedad->color_secundario ?? '#FFFFFF') }}" 
                        pattern="^#[0-9A-Fa-f]{6}$" placeholder="#FFFFFF"
                        class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('color_secundario') border-red-500 @enderror">
                </div>
                @error('color_secundario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea name="descripcion" id="descripcion" rows="3"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $propiedad->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    URL del Logo
                </label>
                <input type="text" name="logo" id="logo" value="{{ old('logo', $propiedad->logo) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('logo') border-red-500 @enderror">
                @error('logo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Información del Administrador -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-user-shield mr-2"></i> Administrador de la Propiedad
        </h2>
        @if($administradorPrincipal && $administradorPrincipal->user)
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-600 mb-2">Administrador Principal:</p>
                <p class="font-medium text-gray-900">{{ $administradorPrincipal->user->nombre }}</p>
                <p class="text-sm text-gray-600">{{ $administradorPrincipal->user->email }}</p>
            </div>
            <p class="text-sm text-gray-600">La información del administrador no se puede editar desde aquí. Use la gestión de usuarios para modificar los datos del administrador.</p>
        @else
            <p class="text-sm text-yellow-600">No se encontró un administrador principal asociado a esta propiedad.</p>
        @endif
    </div>

    <!-- Módulos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-puzzle-piece mr-2"></i> Módulos
        </h2>
        <p class="text-sm text-gray-600 mb-4">Seleccione los módulos que estarán disponibles para esta propiedad <span class="text-red-500">*</span></p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($modulos as $modulo)
                <div class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <input type="checkbox" name="modulos[]" id="modulo_{{ $modulo->id }}" value="{{ $modulo->id }}"
                        {{ in_array($modulo->id, old('modulos', $propiedad->modulos->pluck('id')->toArray())) ? 'checked' : '' }}
                        class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="modulo_{{ $modulo->id }}" class="ml-3 flex-1 cursor-pointer">
                        <div class="font-medium text-gray-900">{{ $modulo->nombre }}</div>
                        <div class="text-sm text-gray-500">{{ $modulo->descripcion }}</div>
                    </label>
                </div>
            @endforeach
        </div>
        @error('modulos')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('modulos.*')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Botones de Acción -->
    <div class="flex justify-end gap-4">
        <a href="{{ route('superadmin.propiedades.index') }}" 
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Cancelar
        </a>
        <button type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-save mr-2"></i> Actualizar Propiedad
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Sincronizar input de color con input de texto
    document.getElementById('color_primario').addEventListener('input', function(e) {
        const textInput = e.target.nextElementSibling;
        textInput.value = e.target.value;
    });
    
    document.querySelector('input[name="color_primario"][type="text"]').addEventListener('input', function(e) {
        const colorInput = e.target.previousElementSibling;
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            colorInput.value = e.target.value;
        }
    });

    document.getElementById('color_secundario').addEventListener('input', function(e) {
        const textInput = e.target.nextElementSibling;
        textInput.value = e.target.value;
    });
    
    document.querySelector('input[name="color_secundario"][type="text"]').addEventListener('input', function(e) {
        const colorInput = e.target.previousElementSibling;
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            colorInput.value = e.target.value;
        }
    });
</script>
@endpush
@endsection
