@extends('emails.layout')

@section('title', '¡Bienvenido a domoPH!')

@section('content')
@php
    $urlLogin = rtrim(env('APP_URL', 'http://localhost'), '/') . '/app/login';
    $propiedad = $residente->unidad->propiedad ?? null;
    $unidad = $residente->unidad;
    
    // Formatear número de unidad
    $numeroUnidad = $unidad->numero;
    if ($unidad->torre) {
        $numeroUnidad .= ' - ' . $unidad->torre;
    }
    if ($unidad->bloque) {
        $numeroUnidad .= ' - ' . $unidad->bloque;
    }
@endphp

    <!-- Saludo Personalizado -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                    ¡Bienvenido a domoPH, {{ $usuario->nombre }}!
                </h1>
                <p style="margin: 12px 0 0 0; font-size: 16px; color: #6b7280; line-height: 1.6;">
                    Tu cuenta ha sido creada exitosamente. Ahora puedes acceder a todas las funcionalidades de <strong style="color: #1f2937;">domoPH</strong> para gestionar tu vida residencial.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje de Bienvenida -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f0f9ff; border-left: 4px solid #2563eb; border-radius: 6px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0; font-size: 15px; color: #1e40af; line-height: 1.6;">
                    <strong>domoPH</strong> es tu aplicación integral para gestionar tu vida en el conjunto residencial. Desde aquí podrás acceder a comunicados, reservas, cartera, PQRS y mucho más.
                </p>
            </td>
        </tr>
    </table>

    @if($propiedad)
    <!-- Información de la Propiedad y Unidad -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Tu Información Residencial
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
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
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Unidad:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $numeroUnidad }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($residente->tipo_relacion)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Tipo de relación:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ ucfirst(str_replace('_', ' ', $residente->tipo_relacion)) }}</span>
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
    @endif

    <!-- Credenciales de Acceso -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #fef3c7; border-radius: 6px; border: 1px solid #fbbf24;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #92400e; line-height: 1.4;">
                    🔐 Credenciales de Acceso
                </h2>
                <p style="margin: 0 0 16px 0; font-size: 14px; color: #78350f; line-height: 1.5;">
                    Usa estas credenciales para acceder a tu aplicación. Te recomendamos cambiar tu contraseña después del primer acceso.
                </p>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #ffffff; border-radius: 6px; border: 1px solid #fbbf24;">
                    <tr>
                        <td style="padding: 16px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="120" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 600;">URL de acceso:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <a href="{{ $urlLogin }}" style="font-size: 14px; color: #2563eb; text-decoration: none; font-weight: 500; word-break: break-all;">{{ $urlLogin }}</a>
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
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 600;">Usuario (Email):</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 500; font-family: 'Courier New', monospace;">{{ $usuario->email }}</span>
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
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 600;">Contraseña:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 500; font-family: 'Courier New', monospace; background-color: #f9fafb; padding: 4px 8px; border-radius: 4px; display: inline-block;">{{ $password }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- CTA Button -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 32px;">
        <tr>
            <td align="center" style="padding-bottom: 8px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td align="center" style="background-color: #1f2937; border-radius: 6px;">
                            <a href="{{ $urlLogin }}" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px; line-height: 1.4;">
                                Acceder a mi Aplicación
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Información Adicional -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 32px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    ¿Qué puedes hacer en domoPH?
                </h2>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #374151; line-height: 1.8;">
                    <li style="margin-bottom: 8px;">Ver y gestionar tu cartera de pagos</li>
                    <li style="margin-bottom: 8px;">Recibir comunicados importantes del conjunto</li>
                    <li style="margin-bottom: 8px;">Reservar zonas comunes (piscina, gimnasio, salón social, etc.)</li>
                    <li style="margin-bottom: 8px;">Consultar y crear PQRS</li>
                    <li style="margin-bottom: 8px;">Acceder al marketplace vecinal (si está activo)</li>
                    <li style="margin-bottom: 8px;">Gestionar visitas y autorizaciones</li>
                    <li style="margin-bottom: 0;">Y mucho más...</li>
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
                    Estamos aquí para hacer tu vida residencial más fácil.<br>
                    <strong style="color: #1f2937;">¡Bienvenido a domoPH!</strong>
                </p>
                <p style="margin: 12px 0 0 0; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                    El equipo de domoPH
                </p>
            </td>
        </tr>
    </table>
@endsection
