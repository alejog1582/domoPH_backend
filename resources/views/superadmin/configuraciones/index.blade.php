@extends('layouts.app')

@section('title', 'Configuraciones Globales - SuperAdmin')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Configuraciones Globales</h1>
    <p class="mt-2 text-sm text-gray-600">Gestiona las configuraciones del sistema</p>
</div>

<form action="{{ route('superadmin.configuraciones.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-lg shadow">
        @forelse($configuraciones as $categoria => $items)
            <div class="border-b border-gray-200 last:border-b-0">
                <div class="px-6 py-4 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $categoria ?: 'General' }}</h2>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    @php
                        $configIndex = 0;
                    @endphp
                    @foreach($items as $config)
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $config->clave }}
                                    @if(!$config->editable)
                                        <span class="text-xs text-gray-400 ml-2">(Solo lectura)</span>
                                    @endif
                                </label>
                                @if($config->descripcion)
                                    <p class="text-xs text-gray-500 mb-2">{{ $config->descripcion }}</p>
                                @endif
                                
                                @if($config->editable)
                                    @if($config->tipo === 'boolean')
                                        <select name="configuraciones[{{ $configIndex }}][valor]" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="true" {{ $config->valor == 'true' || $config->valor == '1' ? 'selected' : '' }}>Sí</option>
                                            <option value="false" {{ $config->valor == 'false' || $config->valor == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    @elseif($config->tipo === 'integer')
                                        <input 
                                            type="number" 
                                            name="configuraciones[{{ $configIndex }}][valor]" 
                                            value="{{ $config->valor }}" 
                                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
                                        >
                                    @elseif($config->tipo === 'json')
                                        <textarea 
                                            name="configuraciones[{{ $configIndex }}][valor]" 
                                            rows="3"
                                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full font-mono text-sm"
                                        >{{ $config->valor }}</textarea>
                                    @else
                                        <input 
                                            type="text" 
                                            name="configuraciones[{{ $configIndex }}][valor]" 
                                            value="{{ $config->valor }}" 
                                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
                                        >
                                    @endif
                                    <input type="hidden" name="configuraciones[{{ $configIndex }}][clave]" value="{{ $config->clave }}">
                                    @php
                                        $configIndex++;
                                    @endphp
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
                                        <p class="text-sm text-gray-600">{{ $config->valor }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-500">
                No hay configuraciones disponibles
            </div>
        @endforelse
    </div>

    <div class="mt-6 flex justify-end">
        <button 
            type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            <i class="fas fa-save mr-2"></i> Guardar Configuraciones
        </button>
    </div>
</form>
@endsection
