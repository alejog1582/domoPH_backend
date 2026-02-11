@extends('emails.layout')

@section('title', 'Nueva cuenta de cobro generada')

@section('content')
@php
    $unidad = $cuentaCobro->unidad;
    $propiedad = $unidad->propiedad ?? null;
    $urlCartera = 'https://domoph.pro/app/cartera';
    
    // Formatear número de unidad
    $numeroUnidad = $unidad->numero;
    if ($unidad->torre) {
        $numeroUnidad .= ' - ' . $unidad->torre;
    }
    if ($unidad->bloque) {
        $numeroUnidad .= ' - ' . $unidad->bloque;
    }
    
    // Formatear período
    $periodo = $cuentaCobro->periodo;
    $fechaEmision = \Carbon\Carbon::parse($cuentaCobro->fecha_emision);
    $fechaVencimiento = \Carbon\Carbon::parse($cuentaCobro->fecha_vencimiento);
    
    // Formatear valores
    $valorTotal = number_format($cuentaCobro->valor_total, 2, ',', '.');
    $valorCuotas = number_format($cuentaCobro->valor_cuotas, 2, ',', '.');
    
    // Calcular saldo pendiente
    $saldoPendiente = $cuentaCobro->calcularSaldoPendiente();
    $saldoPendienteFormateado = number_format($saldoPendiente, 2, ',', '.');
@endphp

    <!-- Saludo Personalizado -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                    Nueva cuenta de cobro generada
                </h1>
                <p style="margin: 12px 0 0 0; font-size: 16px; color: #6b7280; line-height: 1.6;">
                    Se ha generado una nueva cuenta de cobro para tu unidad <strong style="color: #1f2937;">{{ $numeroUnidad }}</strong> correspondiente al período <strong style="color: #1f2937;">{{ $periodo }}</strong>.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje Informativo -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f0f9ff; border-left: 4px solid #2563eb; border-radius: 6px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0; font-size: 15px; color: #1e40af; line-height: 1.6;">
                    Puedes consultar el detalle completo de tu cuenta de cobro y realizar el pago desde la aplicación <strong>domoPH</strong>.
                </p>
            </td>
        </tr>
    </table>

    <!-- Información de la Cuenta de Cobro -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    💰 Resumen de la Cuenta de Cobro
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    @if($propiedad)
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="160" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Conjunto:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $propiedad->nombre }}</span>
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
                                    <td width="160" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Unidad:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $numeroUnidad }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="160" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Período:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $periodo }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="160" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Fecha de emisión:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $fechaEmision->format('d/m/Y') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="160" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Fecha de vencimiento:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span style="font-size: 14px; color: #dc2626; font-weight: 500;">{{ $fechaVencimiento->format('d/m/Y') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="160" style="padding-right: 12px; vertical-align: top;">
                                        <span style="font-size: 14px; color: #6b7280; font-weight: 500;">Estado:</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        @if($cuentaCobro->estado === 'pagada')
                                            <span style="font-size: 14px; color: #16a34a; font-weight: 500;">✅ Pagada</span>
                                        @elseif($cuentaCobro->estado === 'vencida')
                                            <span style="font-size: 14px; color: #dc2626; font-weight: 500;">⚠️ Vencida</span>
                                        @else
                                            <span style="font-size: 14px; color: #f59e0b; font-weight: 500;">⏳ Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Valores -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #fef3c7; border-radius: 6px; border: 1px solid #fbbf24;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #92400e; line-height: 1.4;">
                    💵 Valores
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #ffffff; border-radius: 6px; border: 1px solid #fbbf24;">
                    <tr>
                        <td style="padding: 16px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="160" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 500;">Valor de cuotas:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 500;">${{ $valorCuotas }} COP</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @if($cuentaCobro->valor_intereses > 0)
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="160" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 500;">Intereses:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 500;">${{ number_format($cuentaCobro->valor_intereses, 2, ',', '.') }} COP</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                @if($cuentaCobro->valor_recargos > 0)
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="160" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 500;">Recargos:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 500;">${{ number_format($cuentaCobro->valor_recargos, 2, ',', '.') }} COP</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                @if($cuentaCobro->valor_descuentos > 0)
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="160" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 14px; color: #92400e; font-weight: 500;">Descuentos:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 14px; color: #16a34a; font-weight: 500;">-${{ number_format($cuentaCobro->valor_descuentos, 2, ',', '.') }} COP</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 12px 0 0 0; border-top: 2px solid #fbbf24;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="160" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 16px; color: #92400e; font-weight: 600;">Valor total:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 18px; color: #1f2937; font-weight: 700;">${{ $valorTotal }} COP</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @if($saldoPendiente > 0 && $cuentaCobro->estado !== 'pagada')
                                <tr>
                                    <td style="padding: 8px 0 0 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="160" style="padding-right: 12px; vertical-align: top;">
                                                    <span style="font-size: 14px; color: #dc2626; font-weight: 500;">Saldo pendiente:</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <span style="font-size: 16px; color: #dc2626; font-weight: 600;">${{ $saldoPendienteFormateado }} COP</span>
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
            </td>
        </tr>
    </table>

    @if($cuentaCobro->detalles->count() > 0)
    <!-- Detalles de la Cuenta -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #1f2937; line-height: 1.4;">
                    📋 Detalle de Conceptos
                </h2>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #ffffff; border-radius: 6px;">
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 4px 0;">
                                        <span style="font-size: 13px; color: #6b7280; font-weight: 600;">Concepto</span>
                                    </td>
                                    <td align="right" style="padding: 4px 0;">
                                        <span style="font-size: 13px; color: #6b7280; font-weight: 600;">Valor</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @foreach($cuentaCobro->detalles as $detalle)
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #f3f4f6;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 4px 0;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 400;">{{ $detalle->concepto }}</span>
                                    </td>
                                    <td align="right" style="padding: 4px 0;">
                                        <span style="font-size: 14px; color: #1f2937; font-weight: 500;">${{ number_format($detalle->valor, 2, ',', '.') }} COP</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
    @endif

    <!-- CTA Button -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 32px;">
        <tr>
            <td align="center" style="padding-bottom: 8px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td align="center" style="background-color: #1f2937; border-radius: 6px;">
                            <a href="{{ $urlCartera }}" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px; line-height: 1.4;">
                                Ver Detalle en la Aplicación
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
                    Importante
                </h2>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #374151; line-height: 1.8;">
                    <li style="margin-bottom: 8px;">La fecha de vencimiento es <strong>{{ $fechaVencimiento->format('d/m/Y') }}</strong></li>
                    <li style="margin-bottom: 8px;">Puedes consultar el detalle completo y realizar el pago desde la aplicación</li>
                    <li style="margin-bottom: 0;">Si tienes dudas sobre tu cuenta de cobro, contacta a la administración</li>
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
                    Si tienes alguna pregunta, no dudes en contactarnos.<br>
                    <strong style="color: #1f2937;">¡Gracias por tu atención!</strong>
                </p>
                <p style="margin: 12px 0 0 0; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                    El equipo de domoPH
                </p>
            </td>
        </tr>
    </table>
@endsection
