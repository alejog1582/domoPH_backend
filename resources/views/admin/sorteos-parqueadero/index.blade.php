@extends('admin.layouts.app')

@section('title', 'Sorteos Parqueaderos - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sorteos Parqueaderos</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de sorteos de parqueaderos</p>
        </div>
        <div>
            <a href="{{ route('admin.sorteos-parqueadero.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Crear Nuevo Sorteo
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('admin.sorteos-parqueadero.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Búsqueda -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                placeholder="Título del sorteo..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
            </select>
        </div>

        <!-- Activo -->
        <div>
            <label for="activo" class="block text-sm font-medium text-gray-700 mb-1">Activo</label>
            <select name="activo" id="activo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="todos">Todos</option>
                <option value="si" {{ request('activo') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ request('activo') == 'no' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Fecha Sorteo Desde -->
        <div>
            <label for="fecha_sorteo_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Sorteo Desde</label>
            <input type="date" name="fecha_sorteo_desde" id="fecha_sorteo_desde" value="{{ request('fecha_sorteo_desde') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Fecha Sorteo Hasta -->
        <div>
            <label for="fecha_sorteo_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Sorteo Hasta</label>
            <input type="date" name="fecha_sorteo_hasta" id="fecha_sorteo_hasta" value="{{ request('fecha_sorteo_hasta') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="{{ route('admin.sorteos-parqueadero.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                <i class="fas fa-times mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Cards de Sorteos -->
@if($sorteos->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($sorteos as $sorteo)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                {{ $sorteo->titulo }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($sorteo->estado == 'activo') bg-green-100 text-green-800
                                    @elseif($sorteo->estado == 'cerrado') bg-gray-100 text-gray-800
                                    @elseif($sorteo->estado == 'anulado') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($sorteo->estado) }}
                                </span>
                                @if($sorteo->activo)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($sorteo->descripcion)
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ Str::limit($sorteo->descripcion, 100) }}
                        </p>
                    @endif

                    <!-- Fechas -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                            <span class="font-medium">Inicio recolección:</span>
                            <span class="ml-2">{{ $sorteo->fecha_inicio_recoleccion->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-times mr-2 text-gray-400"></i>
                            <span class="font-medium">Fin recolección:</span>
                            <span class="ml-2">{{ $sorteo->fecha_fin_recoleccion->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-check mr-2 text-gray-400"></i>
                            <span class="font-medium">Fecha sorteo:</span>
                            <span class="ml-2 font-semibold text-blue-600">{{ $sorteo->fecha_sorteo->format('d/m/Y') }}</span>
                        </div>
                        @if($sorteo->hora_sorteo)
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-clock mr-2 text-gray-400"></i>
                            <span class="font-medium">Hora sorteo:</span>
                            <span class="ml-2">{{ substr($sorteo->hora_sorteo, 0, 5) }}</span>
                        </div>
                        @endif
                        @if($sorteo->fecha_inicio_uso)
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-plus mr-2 text-gray-400"></i>
                            <span class="font-medium">Inicio uso:</span>
                            <span class="ml-2">{{ $sorteo->fecha_inicio_uso->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($sorteo->duracion_meses)
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-hourglass-half mr-2 text-gray-400"></i>
                            <span class="font-medium">Duración:</span>
                            <span class="ml-2">{{ $sorteo->duracion_meses }} {{ $sorteo->duracion_meses == 1 ? 'mes' : 'meses' }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Capacidad y Disponibilidad -->
                    @php
                        $participantesAutos = $sorteo->participantes->where('tipo_vehiculo', 'carro')->count();
                        $participantesMotos = $sorteo->participantes->where('tipo_vehiculo', 'moto')->count();
                        $disponibilidadAutos = max(0, ($sorteo->capacidad_autos ?? 0) - $participantesAutos);
                        $disponibilidadMotos = max(0, ($sorteo->capacidad_motos ?? 0) - $participantesMotos);
                    @endphp
                    <div class="mb-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-car mr-2 text-gray-400"></i>
                                <span class="font-medium">Autos:</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-700">
                                    {{ $participantesAutos }} / {{ $sorteo->capacidad_autos ?? 0 }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                    {{ $disponibilidadAutos > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $disponibilidadAutos }} disponibles
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-motorcycle mr-2 text-gray-400"></i>
                                <span class="font-medium">Motos:</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-700">
                                    {{ $participantesMotos }} / {{ $sorteo->capacidad_motos ?? 0 }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                    {{ $disponibilidadMotos > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $disponibilidadMotos }} disponibles
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Participantes -->
                    <div class="mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users mr-2 text-gray-400"></i>
                            <span class="font-medium">Total Participantes:</span>
                            <span class="ml-2">{{ $sorteo->participantes->count() }}</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="text-xs text-gray-500">
                            Creado: {{ $sorteo->created_at->format('d/m/Y') }}
                        </div>
                        <div class="flex gap-2">
                            <button type="button" 
                                onclick="abrirModalIniciarSorteo({{ $sorteo->id }})"
                                class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                                <i class="fas fa-play mr-1"></i>Iniciar Sorteo
                            </button>
                            <a href="{{ route('admin.sorteos-parqueadero.participantes', $sorteo->id) }}" 
                                class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                                <i class="fas fa-users mr-1"></i>Ver Participantes
                            </a>
                            <a href="{{ route('admin.sorteos-parqueadero.edit', $sorteo->id) }}" 
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $sorteos->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-car text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay sorteos registrados</h3>
        <p class="text-sm text-gray-500 mb-6">Comienza creando tu primer sorteo de parqueaderos</p>
        <a href="{{ route('admin.sorteos-parqueadero.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Sorteo
        </a>
    </div>
@endif

<!-- Modal Iniciar Sorteo -->
<div id="modalIniciarSorteo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Iniciar Sorteo</h3>
                <button type="button" onclick="cerrarModalIniciarSorteo()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formIniciarSorteo" method="POST">
                @csrf
                <input type="hidden" name="sorteo_id" id="sorteo_id_modal">
                
                <!-- Tipo de Sorteo -->
                <div class="mb-4">
                    <label for="tipo_sorteo" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Sorteo <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_sorteo" id="tipo_sorteo" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione...</option>
                        <option value="manual">Manual</option>
                        <option value="automatico">Automático</option>
                    </select>
                </div>

                <!-- Balotas Blancas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="balotas_blancas_carro" class="block text-sm font-medium text-gray-700 mb-1">
                            Balotas Blancas Carro
                        </label>
                        <input type="number" name="balotas_blancas_carro" id="balotas_blancas_carro" 
                            value="0" min="0" step="1"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="balotas_blancas_moto" class="block text-sm font-medium text-gray-700 mb-1">
                            Balotas Blancas Moto
                        </label>
                        <input type="number" name="balotas_blancas_moto" id="balotas_blancas_moto" 
                            value="0" min="0" step="1"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Información de Balotas -->
                <div id="infoBalotas" class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                    <h4 class="font-medium text-blue-900 mb-2">Información de Balotas Blancas</h4>
                    <div class="space-y-2 text-sm">
                        <div id="infoAutos" class="text-blue-800">
                            <strong>Autos:</strong>
                            <span id="participantesAutosInfo">0</span> / <span id="capacidadAutosInfo">0</span>
                            <div id="disponibilidadAutosInfo" class="ml-4"></div>
                        </div>
                        <div id="infoMotos" class="text-blue-800">
                            <strong>Motos:</strong>
                            <span id="participantesMotosInfo">0</span> / <span id="capacidadMotosInfo">0</span>
                            <div id="disponibilidadMotosInfo" class="ml-4"></div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="button" onclick="cerrarModalIniciarSorteo()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-play mr-2"></i>Iniciar Sorteo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let sorteoData = {};

function abrirModalIniciarSorteo(sorteoId) {
    // Obtener datos del sorteo mediante AJAX
    fetch(`/admin/sorteos-parqueadero/${sorteoId}/datos-sorteo`)
        .then(response => response.json())
        .then(data => {
            sorteoData = data;
            document.getElementById('sorteo_id_modal').value = sorteoId;
            document.getElementById('balotas_blancas_carro').value = data.balotas_blancas_carro || 0;
            document.getElementById('balotas_blancas_moto').value = data.balotas_blancas_moto || 0;
            
            // Actualizar información
            actualizarInfoBalotas();
            
            // Mostrar modal
            document.getElementById('modalIniciarSorteo').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar datos del sorteo');
        });
}

function cerrarModalIniciarSorteo() {
    document.getElementById('modalIniciarSorteo').classList.add('hidden');
}

function actualizarInfoBalotas() {
    const participantesAutos = sorteoData.participantes_autos || 0;
    const participantesMotos = sorteoData.participantes_motos || 0;
    const capacidadAutos = sorteoData.capacidad_autos || 0;
    const capacidadMotos = sorteoData.capacidad_motos || 0;
    
    document.getElementById('participantesAutosInfo').textContent = participantesAutos;
    document.getElementById('capacidadAutosInfo').textContent = capacidadAutos;
    document.getElementById('participantesMotosInfo').textContent = participantesMotos;
    document.getElementById('capacidadMotosInfo').textContent = capacidadMotos;
    
    // Calcular balotas necesarias
    const balotasNecesariasAutos = Math.max(0, participantesAutos - capacidadAutos);
    const balotasNecesariasMotos = Math.max(0, participantesMotos - capacidadMotos);
    
    const disponibilidadAutos = capacidadAutos - participantesAutos;
    const disponibilidadMotos = capacidadMotos - participantesMotos;
    
    let textoAutos = '';
    if (disponibilidadAutos >= 0) {
        textoAutos = `${disponibilidadAutos} disponibles`;
        if (balotasNecesariasAutos === 0) {
            textoAutos += ' - No se requieren balotas blancas';
        }
    } else {
        textoAutos = `Se requieren ${balotasNecesariasAutos} balotas blancas`;
    }
    
    let textoMotos = '';
    if (disponibilidadMotos >= 0) {
        textoMotos = `${disponibilidadMotos} disponibles`;
        if (balotasNecesariasMotos === 0) {
            textoMotos += ' - No se requieren balotas blancas';
        }
    } else {
        textoMotos = `Se requieren ${balotasNecesariasMotos} balotas blancas`;
    }
    
    document.getElementById('disponibilidadAutosInfo').textContent = textoAutos;
    document.getElementById('disponibilidadMotosInfo').textContent = textoMotos;
}

// Manejar envío del formulario
document.getElementById('formIniciarSorteo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sorteoId = formData.get('sorteo_id');
    const tipoSorteo = formData.get('tipo_sorteo');
    
    fetch(`/admin/sorteos-parqueadero/${sorteoId}/iniciar-sorteo`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.json();
        }
    })
    .then(data => {
        if (data && data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al iniciar el sorteo');
    });
});
</script>
@endsection
