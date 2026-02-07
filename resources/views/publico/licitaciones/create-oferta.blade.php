@extends('publico.layouts.app')

@section('title', 'Enviar Oferta - ' . $licitacion->titulo)

@section('content')
<div class="mb-6">
    <a href="{{ route('licitaciones-publicas.show', $licitacion->id) }}" 
       class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver a Detalles
    </a>
</div>

<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Enviar Oferta</h1>
        <p class="text-gray-600">Completa el formulario para enviar tu oferta a la licitación</p>
        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <h2 class="font-semibold text-gray-900 mb-2">{{ $licitacion->titulo }}</h2>
            <p class="text-sm text-gray-600">{{ $licitacion->copropiedad->nombre }}</p>
        </div>
    </div>

    <form action="{{ route('licitaciones-publicas.store-oferta', $licitacion->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Nombre del Proveedor -->
            <div>
                <label for="nombre_proveedor" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del Proveedor <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre_proveedor" id="nombre_proveedor" value="{{ old('nombre_proveedor') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre_proveedor') border-red-500 @enderror"
                    placeholder="Nombre de la empresa o persona">
                @error('nombre_proveedor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIT del Proveedor -->
            <div>
                <label for="nit_proveedor" class="block text-sm font-medium text-gray-700 mb-1">
                    NIT del Proveedor
                </label>
                <input type="text" name="nit_proveedor" id="nit_proveedor" value="{{ old('nit_proveedor') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nit_proveedor') border-red-500 @enderror"
                    placeholder="Número de identificación tributaria">
                @error('nit_proveedor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email de Contacto -->
            <div>
                <label for="email_contacto" class="block text-sm font-medium text-gray-700 mb-1">
                    Email de Contacto <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email_contacto" id="email_contacto" value="{{ old('email_contacto') }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email_contacto') border-red-500 @enderror"
                    placeholder="correo@ejemplo.com">
                @error('email_contacto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono de Contacto -->
            <div>
                <label for="telefono_contacto" class="block text-sm font-medium text-gray-700 mb-1">
                    Teléfono de Contacto
                </label>
                <input type="text" name="telefono_contacto" id="telefono_contacto" value="{{ old('telefono_contacto') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('telefono_contacto') border-red-500 @enderror"
                    placeholder="300 123 4567">
                @error('telefono_contacto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Valor Ofertado -->
            <div>
                <label for="valor_ofertado" class="block text-sm font-medium text-gray-700 mb-1">
                    Valor Ofertado <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                    <input type="number" name="valor_ofertado" id="valor_ofertado" value="{{ old('valor_ofertado') }}" required
                        step="0.01" min="0"
                        class="w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('valor_ofertado') border-red-500 @enderror"
                        placeholder="0.00">
                </div>
                @error('valor_ofertado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Descripción de la Oferta -->
        <div class="mb-6">
            <label for="descripcion_oferta" class="block text-sm font-medium text-gray-700 mb-1">
                Descripción de la Oferta <span class="text-red-500">*</span>
            </label>
            <textarea name="descripcion_oferta" id="descripcion_oferta" rows="8" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('descripcion_oferta') border-red-500 @enderror"
                placeholder="Describe detalladamente tu oferta, incluyendo servicios, productos, tiempos de entrega, garantías, etc.">{{ old('descripcion_oferta') }}</textarea>
            @error('descripcion_oferta')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Archivos Adjuntos -->
        <div class="mb-6">
            <label for="archivos" class="block text-sm font-medium text-gray-700 mb-1">
                Archivos Adjuntos (Cotizaciones, Propuestas Técnicas, Certificados, etc.)
            </label>
            <input type="file" name="archivos[]" id="archivos" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('archivos.*') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG. Máximo 10MB por archivo.</p>
            @error('archivos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Información Adicional -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <i class="fas fa-info-circle text-yellow-600 mr-3 mt-1"></i>
                <div class="text-sm text-yellow-800">
                    <p class="font-semibold mb-1">Importante:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Una vez enviada, tu oferta será revisada por el administrador.</li>
                        <li>Recibirás notificaciones sobre el estado de tu oferta al correo proporcionado.</li>
                        <li>Asegúrate de incluir todos los documentos necesarios.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('licitaciones-publicas.show', $licitacion->id) }}" 
               class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
                <i class="fas fa-paper-plane mr-2"></i>
                Enviar Oferta
            </button>
        </div>
    </form>
</div>
@endsection
