@extends('admin.layouts.app')

@section('title', 'Detalle de Reserva - Administrador')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Reserva</h1>
            <p class="mt-2 text-sm text-gray-600">ID: <strong>#{{ $reserva->id }}</strong></p>
        </div>
        <a href="{{ route('admin.reservas.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Información Principal (Solo Lectura) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Reserva</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zona Social</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $reserva->zonaSocial->nombre ?? 'N/A' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Reserva</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $reserva->fecha_reserva->format('d/m/Y') }} ({{ $reserva->fecha_reserva->locale('es')->dayName }})
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Horario</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fin, 0, 5) }}
                    @if($reserva->duracion_minutos)
                        ({{ $reserva->duracion_minutos }} minutos)
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    @if($reserva->unidad)
                        {{ $reserva->unidad->numero }} 
                        @if($reserva->unidad->torre) - Torre {{ $reserva->unidad->torre }} @endif 
                        @if($reserva->unidad->bloque) - Bloque {{ $reserva->unidad->bloque }} @endif
                    @else
                        N/A
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Solicitante</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    <div class="font-medium">{{ $reserva->nombre_solicitante }}</div>
                    @if($reserva->email_solicitante)
                        <div class="text-sm text-gray-600">{{ $reserva->email_solicitante }}</div>
                    @endif
                    @if($reserva->telefono_solicitante)
                        <div class="text-sm text-gray-600">{{ $reserva->telefono_solicitante }}</div>
                    @endif
                </div>
            </div>

            @if($reserva->descripcion)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900 whitespace-pre-wrap">
                    {{ $reserva->descripcion }}
                </div>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Invitados</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                    {{ $reserva->cantidad_invitados }} invitado(s)
                </div>
            </div>

            @if($reserva->invitados && $reserva->invitados->count() > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lista de Invitados</label>
                <div class="space-y-2">
                    @foreach($reserva->invitados as $invitado)
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            <div class="font-medium text-gray-900">{{ $invitado->nombre }}</div>
                            @if($invitado->documento)
                                <div class="text-sm text-gray-600">Doc: {{ $invitado->documento }}</div>
                            @endif
                            @if($invitado->telefono)
                                <div class="text-sm text-gray-600">Tel: {{ $invitado->telefono }}</div>
                            @endif
                            @if($invitado->tipo === 'vehicular' && $invitado->placa)
                                <div class="text-sm text-gray-600">Placa: {{ $invitado->placa }}</div>
                            @endif
                            <div class="text-xs text-gray-500 mt-1">
                                Tipo: {{ ucfirst($invitado->tipo) }} | 
                                Estado: {{ ucfirst($invitado->estado) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Información Económica</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Costo de Reserva:</span>
                        <span class="font-medium">${{ number_format($reserva->costo_reserva, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Depósito de Garantía:</span>
                        <span class="font-medium">${{ number_format($reserva->deposito_garantia, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="font-medium text-gray-900">Total:</span>
                        <span class="font-bold text-lg">${{ number_format($reserva->costo_reserva + $reserva->deposito_garantia, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if($reserva->adjuntos && isset($reserva->adjuntos['soporte_pago']))
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Soporte de Pago</label>
                <div class="px-3 py-2 bg-gray-50 rounded-md">
                    <a href="{{ $reserva->adjuntos['soporte_pago']['url'] }}" target="_blank" 
                        class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Ver soporte de pago
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Gestión (Editable) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Gestión</h2>
        <form action="{{ route('admin.reservas.update', $reserva) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select name="estado" id="estado" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estado') border-red-500 @enderror">
                        <option value="solicitada" {{ old('estado', $reserva->estado) == 'solicitada' ? 'selected' : '' }}>Solicitada</option>
                        <option value="aprobada" {{ old('estado', $reserva->estado) == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                        <option value="rechazada" {{ old('estado', $reserva->estado) == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                        <option value="cancelada" {{ old('estado', $reserva->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        <option value="finalizada" {{ old('estado', $reserva->estado) == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                    </select>
                    @error('estado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado de Pago -->
                <div>
                    <label for="estado_pago" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado de Pago
                    </label>
                    <select name="estado_pago" id="estado_pago"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estado_pago') border-red-500 @enderror">
                        <option value="pendiente" {{ old('estado_pago', $reserva->estado_pago) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagado" {{ old('estado_pago', $reserva->estado_pago) == 'pagado' ? 'selected' : '' }}>Pagado</option>
                        <option value="exento" {{ old('estado_pago', $reserva->estado_pago) == 'exento' ? 'selected' : '' }}>Exento</option>
                        <option value="reembolsado" {{ old('estado_pago', $reserva->estado_pago) == 'reembolsado' ? 'selected' : '' }}>Reembolsado</option>
                    </select>
                    @error('estado_pago')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Motivo de Rechazo -->
                <div id="motivo_rechazo_div" style="display: {{ old('estado', $reserva->estado) == 'rechazada' ? 'block' : 'none' }};">
                    <label for="motivo_rechazo" class="block text-sm font-medium text-gray-700 mb-1">
                        Motivo de Rechazo
                    </label>
                    <textarea name="motivo_rechazo" id="motivo_rechazo" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo_rechazo') border-red-500 @enderror">{{ old('motivo_rechazo', $reserva->motivo_rechazo) }}</textarea>
                    @error('motivo_rechazo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Motivo de Cancelación -->
                <div id="motivo_cancelacion_div" style="display: {{ old('estado', $reserva->estado) == 'cancelada' ? 'block' : 'none' }};">
                    <label for="motivo_cancelacion" class="block text-sm font-medium text-gray-700 mb-1">
                        Motivo de Cancelación
                    </label>
                    <textarea name="motivo_cancelacion" id="motivo_cancelacion" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('motivo_cancelacion') border-red-500 @enderror">{{ old('motivo_cancelacion', $reserva->motivo_cancelacion) }}</textarea>
                    @error('motivo_cancelacion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Incumplimiento -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="incumplimiento" value="1" 
                            {{ old('incumplimiento', $reserva->incumplimiento) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Marcar como incumplimiento</span>
                    </label>
                </div>

                <!-- Observaciones Admin -->
                <div>
                    <label for="observaciones_admin" class="block text-sm font-medium text-gray-700 mb-1">
                        Observaciones Administrativas
                    </label>
                    <textarea name="observaciones_admin" id="observaciones_admin" rows="4"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('observaciones_admin') border-red-500 @enderror">{{ old('observaciones_admin', $reserva->observaciones_admin) }}</textarea>
                    @error('observaciones_admin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($reserva->aprobada_por && $reserva->aprobadaPor)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Aprobada Por</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                            {{ $reserva->aprobadaPor->nombre }}
                        </div>
                    </div>
                @endif

                @if($reserva->fecha_aprobacion)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Aprobación</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                            {{ $reserva->fecha_aprobacion->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('admin.reservas.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Mostrar/ocultar campos según el estado seleccionado
    document.getElementById('estado').addEventListener('change', function() {
        const estado = this.value;
        const motivoRechazoDiv = document.getElementById('motivo_rechazo_div');
        const motivoCancelacionDiv = document.getElementById('motivo_cancelacion_div');
        
        motivoRechazoDiv.style.display = estado === 'rechazada' ? 'block' : 'none';
        motivoCancelacionDiv.style.display = estado === 'cancelada' ? 'block' : 'none';
    });
</script>
@endsection
