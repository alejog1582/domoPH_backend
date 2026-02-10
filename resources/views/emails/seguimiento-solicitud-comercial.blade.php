@extends('emails.layout')

@section('title', 'Actualización sobre tu solicitud - domoPH')

@section('content')
@php
    $estadoResultante = match($seguimiento->estado_resultante) {
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En Proceso',
        'contactado' => 'Contactado',
        'cerrado_ganado' => 'Cerrado - Ganado',
        'cerrado_perdido' => 'Cerrado - Perdido',
        default => null,
    };

    $estadoColor = match($seguimiento->estado_resultante) {
        'pendiente' => '#6b7280',
        'en_proceso' => '#f59e0b',
        'contactado' => '#2563eb',
        'cerrado_ganado' => '#10b981',
        'cerrado_perdido' => '#dc2626',
        default => '#6b7280',
    };

    $tipoSolicitud = match($solicitud->tipo_solicitud) {
        'cotizacion' => 'Cotización',
        'demo' => 'Demo',
        'contacto' => 'Contacto',
        default => 'Solicitud',
    };

    // Obtener archivos asociados a la solicitud (todos, no solo del seguimiento)
    $archivos = $solicitud->archivos;
    
    // Función para formatear tamaño de archivo
    $formatearTamaño = function($bytes) {
        if (!$bytes) return '0 bytes';
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    };
@endphp

    <!-- Saludo Personalizado -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                    Hola {{ $solicitud->nombre_contacto }},
                </h1>
                <p style="margin: 12px 0 0 0; font-size: 16px; color: #6b7280; line-height: 1.6;">
                    Queremos informarte que hemos dado respuesta a tu solicitud de <strong style="color: #1f2937;">{{ $tipoSolicitud }}</strong>. Estamos aquí para acompañarte en cada paso del proceso.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje de Acompañamiento -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f0f9ff; border-left: 4px solid #2563eb; border-radius: 6px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0; font-size: 15px; color: #1e40af; line-height: 1.6;">
                    <strong>En domoPH</strong>, nos comprometemos a ser tu aliado en la gestión administrativa. Tu solicitud es importante para nosotros y estamos trabajando para brindarte la mejor atención.
                </p>
            </td>
        </tr>
    </table>

    <!-- Información del Seguimiento -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Actualización de tu Solicitud
                </h2>
                
                <!-- Comentario del Seguimiento -->
                <div style="margin-bottom: 20px; padding: 16px; background-color: #ffffff; border-radius: 6px; border-left: 3px solid #2563eb;">
                    <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $seguimiento->comentario }}</p>
                </div>

                @if($estadoResultante)
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Estado actualizado:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: {{ $estadoColor }}; font-weight: 600;">{{ $estadoResultante }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                @endif

                @if($seguimiento->proximo_contacto)
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Próximo contacto:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $seguimiento->proximo_contacto->format('d/m/Y \a \l\a\s H:i') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                @endif

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Fecha de actualización:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $seguimiento->created_at->format('d/m/Y \a \l\a\s H:i') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Archivos Adjuntos (si existen) -->
    @if($archivos->count() > 0)
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Archivos Relacionados
                </h2>
                <p style="margin: 0 0 16px 0; font-size: 14px; color: #6b7280; line-height: 1.5;">
                    Hemos adjuntado los siguientes archivos relacionados con tu solicitud:
                </p>
                
                @foreach($archivos as $archivo)
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 12px; background-color: #ffffff; border-radius: 6px; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="padding: 16px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <p style="margin: 0 0 4px 0; font-size: 14px; font-weight: 500; color: #1f2937; line-height: 1.4;">
                                            📎 {{ $archivo->nombre_archivo }}
                                        </p>
                                        <p style="margin: 0; font-size: 12px; color: #6b7280; line-height: 1.4;">
                                            {{ $formatearTamaño($archivo->tamaño) }}
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align: middle; padding-left: 16px;">
                                        <a href="{{ $archivo->ruta_archivo }}" target="_blank" style="display: inline-block; padding: 8px 16px; font-size: 13px; font-weight: 600; color: #2563eb; text-decoration: none; border: 1px solid #2563eb; border-radius: 6px; background-color: #ffffff;">
                                            Descargar
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                @endforeach
            </td>
        </tr>
    </table>
    @endif

    <!-- Mensaje de Cierre -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 32px;">
        <tr>
            <td style="padding: 20px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                <p style="margin: 0 0 12px 0; font-size: 15px; color: #374151; line-height: 1.6;">
                    Si tienes alguna pregunta adicional o necesitas más información, no dudes en responder este correo. Estamos aquí para ayudarte.
                </p>
                <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.6;">
                    <strong>Gracias por confiar en domoPH.</strong>
                </p>
            </td>
        </tr>
    </table>

    <!-- CTA Opcional -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding-bottom: 8px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td align="center" style="background-color: #1f2937; border-radius: 6px;">
                            <a href="mailto:{{ env('EMAIL_SUPERADMIN', 'contacto@domoph.pro') }}?subject=Consulta sobre mi solicitud - {{ $solicitud->nombre_contacto }}" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px; line-height: 1.4;">
                                Responder o Contactar
                            </a>
                        </td>
                    </tr>
                </table>
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
                    Saludos cordiales,<br>
                    <strong style="color: #1f2937;">El equipo de domoPH</strong>
                </p>
                @if($seguimiento->usuario)
                <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                    Atentamente,<br>
                    {{ $seguimiento->usuario->nombre }}
                </p>
                @endif
            </td>
        </tr>
    </table>
@endsection
