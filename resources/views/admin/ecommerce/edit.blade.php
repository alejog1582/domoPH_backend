@extends('admin.layouts.app')

@section('title', 'Editar Publicación Ecommerce - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Publicación Ecommerce</h1>
            <p class="mt-2 text-sm text-gray-600">Modifica la información de la publicación</p>
        </div>
        <a href="{{ route('admin.ecommerce.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.ecommerce.update', $publicacion->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de la Publicación</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <option value="">Seleccione un residente</option>
                    @foreach($residentes as $residente)
                        <option value="{{ $residente->id }}" {{ old('residente_id', $publicacion->residente_id) == $residente->id ? 'selected' : '' }}>
                            {{ $residente->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('residente_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Categoría -->
            <div>
                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Categoría <span class="text-red-500">*</span>
                </label>
                <select 
                    id="categoria_id" 
                    name="categoria_id" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('categoria_id') border-red-500 @enderror"
                >
                    <option value="">Seleccione una categoría</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $publicacion->categoria_id) == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('categoria_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo de Publicación -->
            <div>
                <label for="tipo_publicacion" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Publicación <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo_publicacion" 
                    name="tipo_publicacion" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo_publicacion') border-red-500 @enderror"
                >
                    <option value="venta" {{ old('tipo_publicacion', $publicacion->tipo_publicacion) == 'venta' ? 'selected' : '' }}>Venta</option>
                    <option value="arriendo" {{ old('tipo_publicacion', $publicacion->tipo_publicacion) == 'arriendo' ? 'selected' : '' }}>Arriendo</option>
                    <option value="servicio" {{ old('tipo_publicacion', $publicacion->tipo_publicacion) == 'servicio' ? 'selected' : '' }}>Servicio</option>
                    <option value="otro" {{ old('tipo_publicacion', $publicacion->tipo_publicacion) == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo_publicacion')
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
                    <option value="en_revision" {{ old('estado', $publicacion->estado) == 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                    <option value="publicado" {{ old('estado', $publicacion->estado) == 'publicado' ? 'selected' : '' }}>Publicado</option>
                    <option value="pausado" {{ old('estado', $publicacion->estado) == 'pausado' ? 'selected' : '' }}>Pausado</option>
                    <option value="finalizado" {{ old('estado', $publicacion->estado) == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Título -->
            <div class="md:col-span-2">
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                    Título <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="titulo" 
                    name="titulo" 
                    value="{{ old('titulo', $publicacion->titulo) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('titulo') border-red-500 @enderror"
                >
                @error('titulo')
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
                    rows="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror"
                >{{ old('descripcion', $publicacion->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Precio -->
            <div>
                <label for="precio" class="block text-sm font-medium text-gray-700 mb-2">
                    Precio
                </label>
                <input 
                    type="number" 
                    id="precio" 
                    name="precio" 
                    value="{{ old('precio', $publicacion->precio) }}" 
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('precio') border-red-500 @enderror"
                >
                @error('precio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Moneda -->
            <div>
                <label for="moneda" class="block text-sm font-medium text-gray-700 mb-2">
                    Moneda
                </label>
                <select 
                    id="moneda" 
                    name="moneda" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('moneda') border-red-500 @enderror"
                >
                    <option value="COP" {{ old('moneda', $publicacion->moneda ?? 'COP') == 'COP' ? 'selected' : '' }}>COP - Peso Colombiano</option>
                    <option value="USD" {{ old('moneda', $publicacion->moneda) == 'USD' ? 'selected' : '' }}>USD - Dólar</option>
                    <option value="EUR" {{ old('moneda', $publicacion->moneda) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                </select>
                @error('moneda')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Es Negociable -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="es_negociable" 
                        value="1"
                        {{ old('es_negociable', $publicacion->es_negociable) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Precio negociable</span>
                </label>
            </div>

            <!-- Fecha de Publicación -->
            <div>
                <label for="fecha_publicacion" class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de Publicación <span class="text-red-500">*</span>
                </label>
                <input 
                    type="datetime-local" 
                    id="fecha_publicacion" 
                    name="fecha_publicacion" 
                    value="{{ old('fecha_publicacion', \Carbon\Carbon::parse($publicacion->fecha_publicacion)->format('Y-m-d\TH:i')) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha_publicacion') border-red-500 @enderror"
                >
                @error('fecha_publicacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Activo -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="activo" 
                        value="1"
                        {{ old('activo', $publicacion->activo) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Publicación activa</span>
                </label>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Información de Contacto</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($contactos->count() > 0)
                @foreach($contactos as $index => $contacto)
                    <div class="md:col-span-2 border border-gray-200 rounded-md p-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Contacto {{ $index + 1 }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                                <p class="text-sm text-gray-900">{{ $contacto->nombre_contacto }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                                <p class="text-sm text-gray-900">
                                    {{ $contacto->telefono }}
                                    @if($contacto->whatsapp)
                                        <span class="ml-2 text-green-600"><i class="fab fa-whatsapp"></i></span>
                                    @endif
                                </p>
                            </div>
                            @if($contacto->email)
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <p class="text-sm text-gray-900">{{ $contacto->email }}</p>
                            </div>
                            @endif
                            @if($contacto->observaciones)
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Observaciones</label>
                                <p class="text-sm text-gray-900">{{ $contacto->observaciones }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="md:col-span-2 text-sm text-gray-500">
                    No hay contactos registrados para esta publicación.
                </div>
            @endif
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Imágenes de la Publicación</h3>
        <div class="grid grid-cols-1 gap-6">
            <!-- Imágenes existentes -->
            @if(isset($imagenes) && $imagenes->count() > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Imágenes Actuales</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    @foreach($imagenes as $imagen)
                    <div class="relative group">
                        <img src="{{ $imagen->ruta_imagen }}" alt="Imagen {{ $loop->iteration }}" class="w-full h-32 object-cover rounded-md border border-gray-300">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity rounded-md flex items-center justify-center">
                            <span class="text-white opacity-0 group-hover:opacity-100 text-xs">Orden: {{ $imagen->orden }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Agregar nuevas imágenes -->
            <div>
                <label for="imagenes" class="block text-sm font-medium text-gray-700 mb-2">
                    Agregar Nuevas Imágenes
                </label>
                <input 
                    type="file" 
                    id="imagenes" 
                    name="imagenes[]" 
                    multiple
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('imagenes') border-red-500 @enderror"
                    onchange="previewImages(this)"
                >
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPEG, PNG, JPG, GIF, WEBP. Puede seleccionar múltiples imágenes. Tamaño máximo por imagen: 5MB</p>
                @error('imagenes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- Vista previa de nuevas imágenes -->
                <div id="preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 hidden">
                    <!-- Las imágenes se mostrarán aquí -->
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.ecommerce.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Actualizar Publicación
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function previewImages(input) {
        const container = document.getElementById('preview-container');
        container.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            container.classList.remove('hidden');
            
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-32 object-cover rounded-md border border-gray-300';
                    img.alt = `Preview ${index + 1}`;
                    
                    div.appendChild(img);
                    container.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            });
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
