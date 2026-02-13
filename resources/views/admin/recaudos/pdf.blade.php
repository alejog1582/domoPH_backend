<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recaudo - {{ $propiedad->nombre }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            margin: 0;
            size: A4;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        
        .container {
            margin: 2.5cm 5cm 2cm 5cm;
            width: auto;
            box-sizing: border-box;
        }
        
        /* Colores de la propiedad - Usar variables del controlador */
        @php
            $primaryColor = $primaryColor ?? ($propiedad->color_primario ?? '#3b82f6');
            $secondaryColor = $secondaryColor ?? ($propiedad->color_secundario ?? '#10b981');
            $primaryRgb = $primaryRgb ?? [59, 130, 246];
            $secondaryRgb = $secondaryRgb ?? [16, 185, 129];
        @endphp
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid {{ $primaryColor }};
        }
        
        .logo-section {
            flex: 0 0 30%;
        }
        
        .info-section {
            flex: 0 0 65%;
            text-align: right;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: {{ $primaryColor }};
            letter-spacing: 0.5px;
        }
        
        .company-details {
            font-size: 10px;
            line-height: 1.6;
        }
        
        .company-details strong {
            color: {{ $primaryColor }};
            font-weight: 600;
        }
        
        .client-info {
            margin-bottom: 25px;
            padding: 15px;
            background: linear-gradient(135deg, rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.05) 0%, rgba({{ $secondaryRgb[0] }}, {{ $secondaryRgb[1] }}, {{ $secondaryRgb[2] }}, 0.05) 100%);
            border: 1px solid rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.2);
            border-radius: 6px;
        }
        
        .client-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .client-label {
            font-weight: bold;
            width: 120px;
            color: {{ $primaryColor }};
        }
        
        .client-value {
            flex: 1;
        }
        
        .payment-info {
            margin-bottom: 25px;
            padding: 15px;
            background: linear-gradient(135deg, rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.05) 0%, rgba({{ $secondaryRgb[0] }}, {{ $secondaryRgb[1] }}, {{ $secondaryRgb[2] }}, 0.05) 100%);
            border: 1px solid rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.2);
            border-radius: 6px;
        }
        
        .payment-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .payment-label {
            font-weight: bold;
            width: 150px;
            color: {{ $primaryColor }};
        }
        
        .payment-value {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        
        thead {
            background-color: {{ $primaryColor }};
            color: #ffffff;
        }
        
        thead tr {
            background-color: {{ $primaryColor }};
        }
        
        th {
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
            background-color: {{ $primaryColor }};
        }
        
        th.text-right {
            text-align: right;
        }
        
        table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tbody tr:hover {
            background-color: rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.03);
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .total-row {
            background: linear-gradient(135deg, rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.1) 0%, rgba({{ $secondaryRgb[0] }}, {{ $secondaryRgb[1] }}, {{ $secondaryRgb[2] }}, 0.1) 100%) !important;
            font-weight: 700;
            border-top: 2px solid {{ $primaryColor }} !important;
        }
        
        .total-row td {
            border-top: 2px solid {{ $primaryColor }} !important;
            color: {{ $primaryColor }};
        }
        
        .account-info {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 12px 15px;
            background: linear-gradient(135deg, rgba({{ $primaryRgb[0] }}, {{ $primaryRgb[1] }}, {{ $primaryRgb[2] }}, 0.1) 0%, rgba({{ $secondaryRgb[0] }}, {{ $secondaryRgb[1] }}, {{ $secondaryRgb[2] }}, 0.1) 100%);
            border-left: 4px solid {{ $primaryColor }};
            border-radius: 4px;
            font-size: 10px;
        }
        
        .account-info strong {
            color: {{ $primaryColor }};
        }
    </style>
</head>
<body>
    <div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <div class="company-name">{{ $propiedad->nombre }}</div>
            <div class="company-details">
                @if($propiedad->nit)
                    <strong>NIT:</strong> {{ $propiedad->nit }}<br>
                @endif
                @if($propiedad->direccion)
                    {{ $propiedad->direccion }}<br>
                @endif
                @if($propiedad->telefono)
                    <strong>Teléfono:</strong> {{ $propiedad->telefono }}
                @endif
            </div>
        </div>
        <div class="info-section">
            <div class="company-details">
                <strong>Fecha de Pago:</strong> {{ $recaudo->fecha_pago->format('d/m/Y H:i') }}<br>
                <strong>Recaudo No.:</strong> {{ $recaudo->numero_recaudo }}<br>
                <strong>Estado:</strong> {{ ucfirst($recaudo->estado) }}
            </div>
        </div>
    </div>

    <!-- Client Information -->
    <div class="client-info">
        <div class="client-row">
            <span class="client-label">Nombre:</span>
            <span class="client-value">{{ $usuario ? $usuario->nombre : 'N/A' }}</span>
        </div>
        <div class="client-row">
            <span class="client-label">Código:</span>
            <span class="client-value">{{ $unidad->numero }}</span>
        </div>
        <div class="client-row">
            <span class="client-label">Dirección:</span>
            <span class="client-value">
                @if($unidad->torre)
                    Torre {{ $unidad->torre }}
                @endif
                @if($unidad->bloque)
                    Bloque {{ $unidad->bloque }}
                @endif
            </span>
        </div>
        <div class="client-row">
            <span class="client-label">Coeficiente:</span>
            <span class="client-value">{{ number_format($unidad->coeficiente / 10000, 6, '.', '') }}</span>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="payment-info">
        <div class="payment-row">
            <span class="payment-label">Valor Pagado:</span>
            <span class="payment-value"><strong>${{ number_format($recaudo->valor_pagado, 2, ',', '.') }}</strong></span>
        </div>
        <div class="payment-row">
            <span class="payment-label">Tipo de Pago:</span>
            <span class="payment-value">{{ ucfirst($recaudo->tipo_pago) }}</span>
        </div>
        <div class="payment-row">
            <span class="payment-label">Medio de Pago:</span>
            <span class="payment-value">{{ ucfirst($recaudo->medio_pago) }}</span>
        </div>
        @if($recaudo->referencia_pago)
        <div class="payment-row">
            <span class="payment-label">Referencia de Pago:</span>
            <span class="payment-value">{{ $recaudo->referencia_pago }}</span>
        </div>
        @endif
        @if($recaudo->descripcion)
        <div class="payment-row">
            <span class="payment-label">Descripción:</span>
            <span class="payment-value">{{ $recaudo->descripcion }}</span>
        </div>
        @endif
        @if($recaudo->registradoPor)
        <div class="payment-row">
            <span class="payment-label">Registrado por:</span>
            <span class="payment-value">{{ $recaudo->registradoPor->nombre }}</span>
        </div>
        @endif
    </div>

    <!-- Account Information -->
    @if($cuentaCobro)
    <div class="account-info">
        <strong>Cuenta de Cobro Aplicada:</strong> 
        @php
            $fecha = \Carbon\Carbon::createFromFormat('Y-m', $cuentaCobro->periodo);
            $periodoLabel = $fecha->locale('es')->translatedFormat('F Y');
        @endphp
        {{ $periodoLabel }} (Cuenta de Cobro No. {{ $cuentaCobro->id }})
    </div>
    @else
    <div class="account-info">
        <strong>Abono General:</strong> Este recaudo no está aplicado a una cuenta de cobro específica.
    </div>
    @endif

    <!-- Details Table -->
    @if($recaudo->detalles->count() > 0)
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead style="background-color: {{ $primaryColor }}; color: #ffffff;">
            <tr style="background-color: {{ $primaryColor }};">
                <th style="padding: 12px 10px; text-align: left; font-size: 10px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; background-color: {{ $primaryColor }};">Concepto</th>
                <th style="padding: 12px 10px; text-align: right; font-size: 10px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; background-color: {{ $primaryColor }};">Valor Aplicado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recaudo->detalles as $detalle)
            <tr>
                <td>{{ $detalle->concepto }}</td>
                <td class="text-right">${{ number_format($detalle->valor_aplicado, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>${{ number_format($recaudo->valor_pagado, 2, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
    @else
    <div class="account-info">
        <strong>Total del Recaudo:</strong> ${{ number_format($recaudo->valor_pagado, 2, ',', '.') }}
    </div>
    @endif
    </div>
</body>
</html>
