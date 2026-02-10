@extends('emails.layout')

@section('title', 'Mascota registrada en domoPH')

@section('content')
@php
    $residente = $mascota->residente;
    $usuario = $residente->user ?? null;
    $unidad = $mascota->unidad;
    $propiedad = $unidad->propiedad ?? null;
    
    // Formatear número de unidad
    $numeroUnidad = $unidad->numero;
    if ($unidad->torre) {
        $numeroUnidad .= ' - ' . $unidad->torre;
    }
    if ($unidad->bloque) {
        $numeroUnidad .= ' - ' . $unidad->bloque;
    }
    
    // Formatear tipo de mascota
    $tipoMascota = ucfirst($mascota->tipo);
    
    // Formatear sexo
    $sexoMascota = ucfirst($mascota->sexo);
    
    // Formatear tamaño
    $tamanioMascota = $mascota->tamanio ? ucfirst($mascota->tamanio) : 'No especificado';
    
    // Formatear estado de salud
    $estadoSalud = $mascota->estado_salud ? ucfirst(str_replace('_', ' ', $mascota->estado_salud)) : 'No especificado';
@endphp

    <!-- Saludo Personalizado -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                    ¡Mascota registrada exitosamente!
                </h1>
                <p style="margin: 12px 0 0 0; font-size: 16px; color: #6b7280; line-height: 1.6;">
                    Hola {{ $usuario->nombre ?? 'Residente' }}, tu mascota <strong style="color: #1f2937;">{{ $mascota->nombre }}</strong> ha sido registrada correctamente en el sistema de <strong style="color: #1f2937;">domoPH</strong>.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje Informativo -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f0f9ff; border-left: 4px solid #2563eb; border-radius: 6px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0; font-size: 15px; color: #1e40af; line-height: 1.6;">
                    La información de tu mascota ha sido registrada en nuestro sistema. Esto nos permite mantener un registro actualizado y gestionar mejor el bienestar de todas las mascotas en el conjunto residencial.
                </p>
            </td>
        </tr>
    </table>

    <!-- Información de la Mascota -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    🐾 Información de {{ $mascota->nombre }}
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Nombre:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 600;">{{ $mascota->nombre }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Tipo:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $tipoMascota }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($mascota->raza)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Raza:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $mascota->raza }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($mascota->color)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Color:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $mascota->color }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Sexo:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $sexoMascota }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($mascota->fecha_nacimiento)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Fecha de nacimiento:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d/m/Y') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @elseif($mascota->edad_aproximada)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Edad aproximada:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $mascota->edad_aproximada }} año(s)</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($mascota->tamanio)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Tamaño:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $tamanioMascota }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($mascota->numero_chip)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Número de chip:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400; font-family: 'Courier New', monospace;">{{ $mascota->numero_chip }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Información de Salud -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f0fdf4; border-radius: 6px; border: 1px solid #86efac;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #166534; line-height: 1.4;">
                    🏥 Información de Salud
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">Estado de salud:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">{{ $estadoSalud }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">Vacunado:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">
                                            @if($mascota->vacunado)
                                                ✅ Sí
                                            @else
                                                ❌ No
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($mascota->fecha_vigencia_vacunas)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">Vigencia de vacunas:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">{{ \Carbon\Carbon::parse($mascota->fecha_vigencia_vacunas)->format('d/m/Y') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">Esterilizado:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #166534; font-weight: 500;">
                                            @if($mascota->esterilizado)
                                                ✅ Sí
                                            @else
                                                ❌ No
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($propiedad)
    <!-- Información de Ubicación -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    📍 Ubicación
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Conjunto:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $propiedad->nombre }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="140" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Unidad:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $numeroUnidad }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    @if($mascota->observaciones)
    <!-- Observaciones -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #fffbeb; border-radius: 6px; border: 1px solid #fde047;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #854d0e; line-height: 1.4;">
                    📝 Observaciones
                </h2>
                <p style="margin: 0; font-size: 14px; color: #854d0e; line-height: 1.6;">
                    {{ $mascota->observaciones }}
                </p>
            </td>
        </tr>
    </table>
    @endif

    <!-- Información Adicional -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 32px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Importante
                </h2>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #374151; line-height: 1.8;">
                    <li style="margin-bottom: 8px;">Mantén actualizada la información de vacunación de tu mascota</li>
                    <li style="margin-bottom: 8px;">Si necesitas actualizar algún dato, contacta a la administración</li>
                    <li style="margin-bottom: 0;">Recuerda cumplir con las normas de convivencia relacionadas con mascotas</li>
                </ul>
            </td>
        </tr>
    </table>

    <!-- Divider -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 24px 0 8px 0;">
                <div style="height: 1px; background-color: #e5e7eb;"></div>
            </td>
        </tr>
    </table>

    <!-- Closing -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-top: 8px;">
                <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.5;">
                    Si tienes alguna pregunta o necesitas actualizar la información, no dudes en contactarnos.<br>
                    <strong style="color: #1f2937;">¡Gracias por mantener actualizado el registro!</strong>
                </p>
                <p style="margin: 12px 0 0 0; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                    El equipo de domoPH
                </p>
            </td>
        </tr>
    </table>
@endsection
