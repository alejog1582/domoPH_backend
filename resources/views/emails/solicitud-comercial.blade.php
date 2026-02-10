@extends('emails.layout')

@section('title', 'Nueva Solicitud Comercial - domoPH')

@section('content')
@php
    $tipoSolicitud = match($solicitud->tipo_solicitud) {
        'cotizacion' => 'Cotización',
        'demo' => 'Demo',
        'contacto' => 'Contacto',
        default => 'Solicitud',
    };

    $origen = match($solicitud->origen) {
        'landing' => 'Landing Page',
        'web' => 'Sitio Web',
        'whatsapp' => 'WhatsApp',
        'referido' => 'Referido',
        'otro' => 'Otro',
        default => 'No especificado',
    };

    $prioridad = match($solicitud->prioridad) {
        'alta' => 'Alta',
        'media' => 'Media',
        'baja' => 'Baja',
        default => 'Media',
    };

    $prioridadColor = match($solicitud->prioridad) {
        'alta' => '#dc2626',
        'media' => '#f59e0b',
        'baja' => '#10b981',
        default => '#6b7280',
    };

    $urlAdmin = config('app.url_backend') . '/superadmin/solicitudes-comerciales/' . $solicitud->id;
    $appUrl = config('app.url');
@endphp
    <!-- Title -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                    Nueva Solicitud Comercial
                </h1>
                <p style="margin: 8px 0 0 0; font-size: 16px; color: #6b7280; line-height: 1.5;">
                    Se ha recibido una nueva solicitud de <strong style="color: #1f2937;">{{ $tipoSolicitud }}</strong> en el sistema.
                </p>
            </td>
        </tr>
    </table>

    <!-- Information Card: Contact -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Información del Contacto
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Nombre:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $solicitud->nombre_contacto }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($solicitud->empresa)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Empresa:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $solicitud->empresa }}</span>
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
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Email:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <a href="mailto:{{ $solicitud->email }}" style="font-size: 14px; color: #2563eb; text-decoration: none; font-weight: 400;">{{ $solicitud->email }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Teléfono:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <a href="tel:{{ $solicitud->telefono }}" style="font-size: 14px; color: #2563eb; text-decoration: none; font-weight: 400;">{{ $solicitud->telefono }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($solicitud->ciudad)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Ciudad:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $solicitud->ciudad }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($solicitud->pais)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">País:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $solicitud->pais }}</span>
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

    <!-- Information Card: Request Details -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Detalles de la Solicitud
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Tipo:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $tipoSolicitud }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Origen:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $origen }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Prioridad:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: {{ $prioridadColor }}; font-weight: 600;">{{ $prioridad }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Estado:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">Pendiente</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Fecha:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $solicitud->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Message Card -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 32px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Mensaje
                </h2>
                <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $solicitud->mensaje }}</p>
            </td>
        </tr>
    </table>

    <!-- CTA Button -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding-bottom: 8px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td align="center" style="background-color: #1f2937; border-radius: 6px;">
                            <a href="{{ $urlAdmin }}" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px; line-height: 1.4;">
                                Ver Solicitud en el Panel
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
                    Saludos,<br>
                    <strong style="color: #1f2937;">{{ config('app.name') }}</strong>
                </p>
            </td>
        </tr>
    </table>
@endsection
