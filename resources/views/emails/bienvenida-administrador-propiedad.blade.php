@extends('emails.layout')

@section('title', 'Bienvenido a domoPH - Acceso a tu panel administrativo')

@section('content')
@php
    $urlLogin = config('app.url_backend', config('app.url_backend')) . '/admin/login';
@endphp

    <!-- Saludo Personalizado -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                    ¡Bienvenido a domoPH, {{ $administrador->nombre }}!
                </h1>
                <p style="margin: 12px 0 0 0; font-size: 16px; color: #6b7280; line-height: 1.6;">
                    Tu cuenta de administrador ha sido creada exitosamente para gestionar <strong style="color: #1f2937;">{{ $propiedad->nombre }}</strong>.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje de Bienvenida -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f0f9ff; border-left: 4px solid #2563eb; border-radius: 6px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0; font-size: 15px; color: #1e40af; line-height: 1.6;">
                    <strong>domoPH</strong> es tu aliado en la gestión administrativa de conjuntos residenciales. Estamos emocionados de acompañarte en este proceso y ayudarte a optimizar la administración de tu propiedad.
                </p>
            </td>
        </tr>
    </table>

    <!-- Información de la Propiedad -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    Información de tu Propiedad
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
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $propiedad->nombre }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($propiedad->nit)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">NIT:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $propiedad->nit }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($propiedad->direccion)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Dirección:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $propiedad->direccion }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($propiedad->ciudad)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="120" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Ciudad:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $propiedad->ciudad }}</span>
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

    <!-- Credenciales de Acceso -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #fef3c7; border-radius: 6px; border: 1px solid #fbbf24;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #92400e; line-height: 1.4;">
                    🔐 Credenciales de Acceso
                </h2>
                <p style="margin: 0 0 16px 0; font-size: 14px; color: #78350f; line-height: 1.5;">
                    Guarda esta información de forma segura. Te recomendamos cambiar tu contraseña después del primer acceso.
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
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 500; font-family: 'Courier New', monospace;">{{ $administrador->email }}</span>
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
                                Acceder a mi Panel Administrativo
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
                    Próximos Pasos
                </h2>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #374151; line-height: 1.8;">
                    <li style="margin-bottom: 8px;">Accede a tu panel administrativo usando las credenciales proporcionadas</li>
                    <li style="margin-bottom: 8px;">Cambia tu contraseña por una más segura en tu perfil</li>
                    <li style="margin-bottom: 8px;">Explora las funcionalidades disponibles según tu plan</li>
                    <li style="margin-bottom: 0;">Si tienes dudas, nuestro equipo de soporte está disponible para ayudarte</li>
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
                    Estamos aquí para apoyarte en cada paso.<br>
                    <strong style="color: #1f2937;">¡Bienvenido a domoPH!</strong>
                </p>
                <p style="margin: 12px 0 0 0; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                    El equipo de domoPH
                </p>
            </td>
        </tr>
    </table>
@endsection
