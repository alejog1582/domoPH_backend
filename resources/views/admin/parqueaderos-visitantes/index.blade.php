@extends('admin.layouts.app')

@section('title', 'Parqueaderos Visitantes - Administrador')

@section('content')
@php
    $propiedad = \App\Helpers\AdminHelper::getPropiedadActiva();
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Parqueaderos de Visitantes</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de parqueaderos de visitantes</p>
        </div>
    </div>
</div>

@if($propiedad)
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Parqueaderos para Carros -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
            <div class="flex items-center">
                <i class="fas fa-car text-blue-600 text-xl mr-3"></i>
                <h2 class="text-lg font-semibold text-blue-900">Parqueaderos para Carros</h2>
            </div>
        </div>

        <div class="p-6">
            <!-- Disponibles -->
            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    Disponibles ({{ $parqueaderosAgrupados['carro']['disponible']->count() }})
                </h3>
                @if($parqueaderosAgrupados['carro']['disponible']->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($parqueaderosAgrupados['carro']['disponible'] as $parqueadero)
                            <div 
                                class="border border-green-300 rounded-lg p-4 bg-green-50 hover:bg-green-100 transition-colors cursor-pointer"
                                onclick="abrirModalVisita({{ $parqueadero->id }}, '{{ $parqueadero->codigo }}', 'carro')"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-900">{{ $parqueadero->codigo }}</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-200 text-green-800">
                                        Disponible
                                    </span>
                                </div>
                                @if($parqueadero->nivel)
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-layer-group mr-1"></i>{{ $parqueadero->nivel }}
                                    </p>
                                @endif
                                @if($parqueadero->es_cubierto)
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-umbrella mr-1"></i>Cubierto
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No hay parqueaderos disponibles para carros.</p>
                @endif
            </div>

            <!-- Ocupados -->
            <div>
                <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    Ocupados ({{ $parqueaderosAgrupados['carro']['ocupado']->count() }})
                </h3>
                @if($parqueaderosAgrupados['carro']['ocupado']->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($parqueaderosAgrupados['carro']['ocupado'] as $parqueadero)
                            <div 
                                class="border border-red-300 rounded-lg p-4 bg-red-50 hover:bg-red-100 transition-colors cursor-pointer"
                                onclick="abrirModalVisitaOcupada({{ $parqueadero->id }})"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-900">{{ $parqueadero->codigo }}</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-200 text-red-800">
                                        Ocupado
                                    </span>
                                </div>
                                @if($parqueadero->nivel)
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-layer-group mr-1"></i>{{ $parqueadero->nivel }}
                                    </p>
                                @endif
                                @if($parqueadero->es_cubierto)
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-umbrella mr-1"></i>Cubierto
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No hay parqueaderos ocupados para carros.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Parqueaderos para Motos -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 bg-orange-50 border-b border-orange-200">
            <div class="flex items-center">
                <i class="fas fa-motorcycle text-orange-600 text-xl mr-3"></i>
                <h2 class="text-lg font-semibold text-orange-900">Parqueaderos para Motos</h2>
            </div>
        </div>

        <div class="p-6">
            <!-- Disponibles -->
            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    Disponibles ({{ $parqueaderosAgrupados['moto']['disponible']->count() }})
                </h3>
                @if($parqueaderosAgrupados['moto']['disponible']->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($parqueaderosAgrupados['moto']['disponible'] as $parqueadero)
                            <div 
                                class="border border-green-300 rounded-lg p-4 bg-green-50 hover:bg-green-100 transition-colors cursor-pointer"
                                onclick="abrirModalVisita({{ $parqueadero->id }}, '{{ $parqueadero->codigo }}', 'moto')"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-900">{{ $parqueadero->codigo }}</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-200 text-green-800">
                                        Disponible
                                    </span>
                                </div>
                                @if($parqueadero->nivel)
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-layer-group mr-1"></i>{{ $parqueadero->nivel }}
                                    </p>
                                @endif
                                @if($parqueadero->es_cubierto)
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-umbrella mr-1"></i>Cubierto
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No hay parqueaderos disponibles para motos.</p>
                @endif
            </div>

            <!-- Ocupados -->
            <div>
                <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    Ocupados ({{ $parqueaderosAgrupados['moto']['ocupado']->count() }})
                </h3>
                @if($parqueaderosAgrupados['moto']['ocupado']->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($parqueaderosAgrupados['moto']['ocupado'] as $parqueadero)
                            <div 
                                class="border border-red-300 rounded-lg p-4 bg-red-50 hover:bg-red-100 transition-colors cursor-pointer"
                                onclick="abrirModalVisitaOcupada({{ $parqueadero->id }})"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-900">{{ $parqueadero->codigo }}</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-200 text-red-800">
                                        Ocupado
                                    </span>
                                </div>
                                @if($parqueadero->nivel)
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-layer-group mr-1"></i>{{ $parqueadero->nivel }}
                                    </p>
                                @endif
                                @if($parqueadero->es_cubierto)
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-umbrella mr-1"></i>Cubierto
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No hay parqueaderos ocupados para motos.</p>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center py-12">
            <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
            <p class="text-gray-600">No hay propiedad asignada.</p>
        </div>
    </div>
@endif

<!-- Modal para Registrar Visita -->
<div id="modalVisita" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Registrar Visita</h3>
                <button onclick="cerrarModalVisita()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="formVisita" onsubmit="guardarVisita(event)">
                @csrf
                <input type="hidden" name="parqueadero_id" id="parqueadero_id_modal">
                <input type="hidden" name="tipo_visita" value="vehicular">
                
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <strong>Parqueadero seleccionado:</strong> 
                        <span id="parqueadero_codigo_modal" class="font-semibold"></span>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Unidad -->
                    <div>
                        <label for="unidad_id_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Unidad <span class="text-red-500">*</span>
                        </label>
                        <select name="unidad_id" id="unidad_id_modal" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccione...</option>
                            @foreach($unidades as $unidad)
                                <option value="{{ $unidad->id }}">
                                    {{ $unidad->numero }} @if($unidad->torre) - Torre {{ $unidad->torre }} @endif @if($unidad->bloque) - Bloque {{ $unidad->bloque }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Residente -->
                    <div>
                        <label for="residente_id_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Residente
                        </label>
                        <select name="residente_id" id="residente_id_modal"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccione...</option>
                            @foreach($residentes as $residente)
                                <option value="{{ $residente->id }}" data-unidad-id="{{ $residente->unidad_id }}">
                                    {{ $residente->user->nombre ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nombre Visitante -->
                    <div>
                        <label for="nombre_visitante_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Visitante <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre_visitante" id="nombre_visitante_modal" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Documento Visitante -->
                    <div>
                        <label for="documento_visitante_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Documento del Visitante
                        </label>
                        <input type="text" name="documento_visitante" id="documento_visitante_modal"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Placa Vehículo -->
                    <div>
                        <label for="placa_vehiculo_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Placa del Vehículo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="placa_vehiculo" id="placa_vehiculo_modal" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Fecha Ingreso -->
                    <div>
                        <label for="fecha_ingreso_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha y Hora de Ingreso <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="fecha_ingreso" id="fecha_ingreso_modal" required
                            value="{{ now()->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Motivo -->
                    <div class="md:col-span-2">
                        <label for="motivo_modal" class="block text-sm font-medium text-gray-700 mb-1">
                            Motivo
                        </label>
                        <input type="text" name="motivo" id="motivo_modal"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mb-4">
                    <label for="observaciones_modal" class="block text-sm font-medium text-gray-700 mb-1">
                        Observaciones
                    </label>
                    <textarea name="observaciones" id="observaciones_modal" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" onclick="cerrarModalVisita()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Guardar Visita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalVisita(parqueaderoId, parqueaderoCodigo, tipoVehiculo) {
        document.getElementById('parqueadero_id_modal').value = parqueaderoId;
        document.getElementById('parqueadero_codigo_modal').textContent = parqueaderoCodigo;
        document.getElementById('modalVisita').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalVisita() {
        document.getElementById('modalVisita').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('formVisita').reset();
    }

    // Filtrar residentes por unidad seleccionada
    document.getElementById('unidad_id_modal').addEventListener('change', function() {
        const unidadId = this.value;
        const residenteSelect = document.getElementById('residente_id_modal');
        const options = residenteSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const unidadIdOption = option.getAttribute('data-unidad-id');
                if (unidadIdOption === unidadId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        });
        
        // Resetear selección de residente
        residenteSelect.value = '';
    });

    async function guardarVisita(event) {
        event.preventDefault();
        
        const form = document.getElementById('formVisita');
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Deshabilitar botón y mostrar loading
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const response = await fetch('{{ route("admin.parqueaderos-visitantes.store-visita") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                alert('Visita registrada correctamente.');
                // Recargar la página para actualizar los estados
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo registrar la visita'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al registrar la visita. Por favor, intente nuevamente.');
        } finally {
            // Restaurar botón
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    }

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('modalVisita').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalVisita();
        }
    });
</script>

<!-- Modal para Ver/Administrar Visita Ocupada -->
<div id="modalVisitaOcupada" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Información de la Visita</h3>
                <button onclick="cerrarModalVisitaOcupada()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="contenidoVisitaOcupada">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                    <p class="mt-2 text-gray-600">Cargando información...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let visitaActual = null;
    let liquidacionActual = null;
    let cobroActivo = false;
    let pasoActual = 1; // 1: Preliquidación, 2: Liquidación real

    async function abrirModalVisitaOcupada(parqueaderoId) {
        document.getElementById('modalVisitaOcupada').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Mostrar loading
        document.getElementById('contenidoVisitaOcupada').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                <p class="mt-2 text-gray-600">Cargando información...</p>
            </div>
        `;

        try {
            const response = await fetch(`{{ url('admin/parqueaderos-visitantes/visita') }}/${parqueaderoId}`);
            const data = await response.json();

            if (data.success) {
                visitaActual = data.data.visita;
                liquidacionActual = data.data.liquidacion;
                cobroActivo = data.data.cobro_activo;
                pasoActual = 1; // Resetear al paso 1
                
                mostrarInformacionVisita();
            } else {
                alert('Error: ' + (data.message || 'No se pudo cargar la información'));
                cerrarModalVisitaOcupada();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al cargar la información. Por favor, intente nuevamente.');
            cerrarModalVisitaOcupada();
        }
    }

    function mostrarInformacionVisita() {
        const fechaIngreso = new Date(visitaActual.fecha_ingreso).toLocaleString('es-ES');
        const unidadTexto = visitaActual.unidad ? 
            `${visitaActual.unidad.numero}${visitaActual.unidad.torre ? ' - Torre ' + visitaActual.unidad.torre : ''}${visitaActual.unidad.bloque ? ' - Bloque ' + visitaActual.unidad.bloque : ''}` : 
            'N/A';
        const residenteTexto = visitaActual.residente && visitaActual.residente.user ? 
            visitaActual.residente.user.nombre : 
            'No asignado';

        let contenido = `
            <div class="space-y-4">
                <!-- Información General -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Información General</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-600">Visitante:</span>
                            <span class="font-medium ml-2">${visitaActual.nombre_visitante}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Documento:</span>
                            <span class="font-medium ml-2">${visitaActual.documento_visitante || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Unidad:</span>
                            <span class="font-medium ml-2">${unidadTexto}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Residente:</span>
                            <span class="font-medium ml-2">${residenteTexto}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Placa:</span>
                            <span class="font-medium ml-2">${visitaActual.placa_vehiculo || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Fecha Ingreso:</span>
                            <span class="font-medium ml-2">${fechaIngreso}</span>
                        </div>
                        ${visitaActual.motivo ? `
                        <div class="md:col-span-2">
                            <span class="text-gray-600">Motivo:</span>
                            <span class="font-medium ml-2">${visitaActual.motivo}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
        `;

        if (cobroActivo && liquidacionActual) {
            // CASO: cobro_parq_visitantes = true
            const minutosGracia = liquidacionActual.minutos_gracia || 0;
            const valorMinuto = parseFloat(liquidacionActual.valor_minuto || 0);
            const estadoLiquidacion = liquidacionActual.estado;
            const tieneHoraSalida = liquidacionActual.hora_salida !== null;

            if (pasoActual === 1) {
                // PASO 1: Preliquidación (sin persistencia)
                // Calcular preliquidación en tiempo real
                const horaActual = new Date();
                const horaLLegada = new Date(liquidacionActual.hora_llegada);
                const minutosTotales = Math.floor((horaActual - horaLLegada) / (1000 * 60));
                const minutosCobrados = Math.max(0, minutosTotales - minutosGracia);
                // Calcular valor total con precisión: minutos cobrados × valor por minuto
                const valorTotal = Math.round(minutosCobrados * valorMinuto * 100) / 100;

                contenido += `
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Preliquidación (Información)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Hora Llegada:</span>
                                <span class="font-medium ml-2">${new Date(liquidacionActual.hora_llegada).toLocaleString('es-ES')}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Hora Salida:</span>
                                <span class="font-medium ml-2">Pendiente</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Minutos Totales:</span>
                                <span class="font-medium ml-2">${minutosTotales} min</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Minutos Gracia:</span>
                                <span class="font-medium ml-2">${minutosGracia} min</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Minutos Cobrados:</span>
                                <span class="font-medium ml-2">${minutosCobrados} min</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Valor por Minuto:</span>
                                <span class="font-medium ml-2">$${valorMinuto.toFixed(2)}</span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-600">Valor Total:</span>
                                <span class="font-bold text-lg text-green-600 ml-2">$${valorTotal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                <span class="text-xs text-gray-500 ml-2">(Calculado en tiempo real)</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button 
                            onclick="finalizarPago()" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <i class="fas fa-check mr-2"></i>Finalizar Pago
                        </button>
                    </div>
                `;
            } else if (pasoActual === 2) {
                // PASO 2: Liquidación real (después de hacer click en "Finalizar Pago")
                const minutosTotales = liquidacionActual.minutos_totales || 0;
                const minutosCobrados = liquidacionActual.minutos_cobrados || 0;
                const valorTotal = parseFloat(liquidacionActual.valor_total || 0);
                const horaSalida = new Date(liquidacionActual.hora_salida).toLocaleString('es-ES');

                contenido += `
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <h4 class="font-semibold text-gray-900 mb-3">Resumen Final de Liquidación</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Hora Llegada:</span>
                                <span class="font-medium ml-2">${new Date(liquidacionActual.hora_llegada).toLocaleString('es-ES')}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Hora Salida:</span>
                                <span class="font-medium ml-2">${horaSalida}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Minutos Totales:</span>
                                <span class="font-medium ml-2">${minutosTotales} min</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Minutos Gracia:</span>
                                <span class="font-medium ml-2">${minutosGracia} min</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Minutos Cobrados:</span>
                                <span class="font-medium ml-2">${minutosCobrados} min</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Valor por Minuto:</span>
                                <span class="font-medium ml-2">$${valorMinuto.toFixed(2)}</span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-600">Valor Total:</span>
                                <span class="font-bold text-lg text-green-600 ml-2">$${valorTotal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <h4 class="font-semibold text-gray-900 mb-3">Registrar Pago</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                                <select id="metodo_pago" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="billetera_virtual">Billetera Virtual</option>
                                </select>
                            </div>
                            <button 
                                onclick="recibirPago()" 
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            >
                                <i class="fas fa-money-bill-wave mr-2"></i>Recibí Dinero
                            </button>
                        </div>
                    </div>
                `;
            }
        } else {
            // CASO: cobro_parq_visitantes = false
            // Solo mostrar información y botón "Finalizar Visita"
            if (visitaActual.estado === 'activa') {
                contenido += `
                    <div class="flex justify-end gap-3">
                        <button 
                            onclick="finalizarVisita()" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <i class="fas fa-check mr-2"></i>Finalizar Visita
                        </button>
                    </div>
                `;
            }
        }

        contenido += `</div>`;

        document.getElementById('contenidoVisitaOcupada').innerHTML = contenido;
    }

    function cerrarModalVisitaOcupada() {
        document.getElementById('modalVisitaOcupada').classList.add('hidden');
        document.body.style.overflow = 'auto';
        visitaActual = null;
        liquidacionActual = null;
        cobroActivo = false;
        pasoActual = 1;
    }

    async function finalizarPago() {
        if (!visitaActual || !cobroActivo) return;

        try {
            const response = await fetch(`{{ url('admin/parqueaderos-visitantes/finalizar-pago') }}/${visitaActual.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                // Actualizar datos y pasar al paso 2
                visitaActual = data.data.visita;
                liquidacionActual = data.data.liquidacion;
                pasoActual = 2;
                mostrarInformacionVisita();
            } else {
                alert('Error: ' + (data.message || 'No se pudo finalizar el pago'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al finalizar el pago. Por favor, intente nuevamente.');
        }
    }

    async function finalizarVisita() {
        if (!visitaActual || cobroActivo) return;

        if (!confirm('¿Está seguro de finalizar esta visita?')) {
            return;
        }

        try {
            const response = await fetch(`{{ url('admin/parqueaderos-visitantes/finalizar-visita') }}/${visitaActual.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Visita finalizada correctamente.');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo finalizar la visita'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al finalizar la visita. Por favor, intente nuevamente.');
        }
    }

    async function recibirPago() {
        if (!liquidacionActual) return;

        const metodoPago = document.getElementById('metodo_pago').value;
        if (!metodoPago) {
            alert('Por favor, seleccione un método de pago.');
            return;
        }

        const valorTotal = parseFloat(liquidacionActual.valor_total || 0);
        if (!confirm('¿Confirmar el pago de $' + valorTotal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '?')) {
            return;
        }

        try {
            const response = await fetch(`{{ url('admin/parqueaderos-visitantes/recibir-pago') }}/${liquidacionActual.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    metodo_pago: metodoPago
                })
            });

            const data = await response.json();

            if (data.success) {
                alert('Pago registrado correctamente.');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo registrar el pago'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al registrar el pago. Por favor, intente nuevamente.');
        }
    }

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('modalVisitaOcupada').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalVisitaOcupada();
        }
    });
</script>

@endsection
