<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta de Cobro - {{ $propiedad->nombre }}</title>
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
        
        .billing-info {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .billing-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .billing-label {
            font-weight: bold;
            width: 150px;
        }
        
        .billing-value {
            flex: 1;
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
        
        .discount-info {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 12px 15px;
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 235, 59, 0.1) 100%);
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            font-size: 10px;
        }
        
        .discount-info strong {
            color: #856404;
        }
        
        .notes-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            line-height: 1.7;
        }
        
        .notes-section p {
            margin-bottom: 12px;
            text-align: justify;
            color: #374151;
        }
        
        .notes-section strong {
            font-weight: 600;
            color: {{ $primaryColor }};
        }
        
        .company-details strong {
            color: {{ $primaryColor }};
            font-weight: 600;
        }
        
        .client-label {
            color: {{ $primaryColor }};
        }
        
        .comentario-cuenta-cobro {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .comentario-cuenta-cobro p {
            margin-bottom: 8px;
        }
        
        .comentario-cuenta-cobro strong {
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
                <strong>Mes:</strong> {{ $periodoActual->locale('es')->translatedFormat('F Y') }}<br>
                <strong>Fecha:</strong> {{ $cuentaCobro->fecha_emision->format('d/m/y') }}<br>
                <strong>Cuenta de Cobro No.:</strong> {{ $cuentaCobro->id }}<br>
            </div>
        </div>
    </div>

    <!-- Client Information -->
    <div class="client-info">
        <div class="client-row">
            <span class="client-label">Nombre:</span>
            <span class="client-value">{{ $usuario ? $usuario->nombre : 'N/A' }} /</span>
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

    <!-- Billing Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead style="background-color: {{ $primaryColor }}; color: #ffffff;">
            <tr style="background-color: {{ $primaryColor }};">
                <th style="padding: 12px 10px; text-align: left; font-size: 10px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; background-color: {{ $primaryColor }};">Concepto</th>
                <th style="padding: 12px 10px; text-align: right; font-size: 10px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; background-color: {{ $primaryColor }};">Saldo {{ $periodoAnterior->locale('es')->translatedFormat('M / y') }}</th>
                <th style="padding: 12px 10px; text-align: right; font-size: 10px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; background-color: {{ $primaryColor }};">Cuotas {{ $periodoActual->locale('es')->translatedFormat('M / y') }}</th>
                <th style="padding: 12px 10px; text-align: right; font-size: 10px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; background-color: {{ $primaryColor }};">Nuevo Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($conceptos as $concepto)
            <tr>
                <td>{{ $concepto['concepto'] }}</td>
                <td class="text-right">${{ number_format($concepto['saldo_anterior'], 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format($concepto['cuota_mes'], 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format($concepto['nuevo_saldo'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Total Mes Sin Descuento</strong></td>
                <td class="text-right"><strong>${{ number_format($saldoAnterior, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>${{ number_format($totalCuotasMes + $totalInteresesMes, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>${{ number_format($totalSinDescuento, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Discount Information -->
    @if($descuento > 0)
    <div class="discount-info">
        <strong>Con Descuento {{ number_format($porcentajeDescuento, 1) }}% hasta {{ $fechaLimiteDescuento->format('M/d/y') }} (${{ number_format($descuento, 0, ',', '.') }}) ......... ${{ number_format($totalConDescuento, 0, ',', '.') }}</strong>
    </div>
    @endif

    <!-- Notes Section -->
    <div class="notes-section">
        {!! $comentarioCuentaCobro ?? '<p><strong>¡Gracias por su pago y por contribuir al buen funcionamiento de la copropiedad!</strong></p>' !!}
    </div>
    </div>
</body>
</html>
