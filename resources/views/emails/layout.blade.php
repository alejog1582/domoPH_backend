<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'domoPH')</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, a { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f5f6f8; font-family: Arial, Helvetica, 'Helvetica Neue', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <!-- Wrapper Table -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f6f8; margin: 0; padding: 0;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin: 0 auto;">
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 30px 40px;">
                            @php
                                // Construir URL absoluta del logo
                                // Opción 1: URL de Cloudinary si está disponible
                                $logoUrl = env('EMAIL_LOGO_URL', null);
                                
                                // Opción 2: URL local absoluta
                                if (!$logoUrl) {
                                    $appUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
                                    $logoUrl = $appUrl . '/imagenes/logo.png';
                                }
                            @endphp
                            <img src="{{ $logoUrl }}" alt="domoPH" width="180" style="max-width: 180px; height: auto; display: block; border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;" />
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            @yield('content')
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 30px 40px; border-top: 1px solid #e5e7eb; background-color: #f9fafb;">
                            <p style="margin: 0; font-size: 12px; color: #6b7280; line-height: 1.5;">
                                © {{ date('Y') }} <strong style="color: #1f2937;">domoPH</strong>. Todos los derechos reservados.
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 12px; color: #9ca3af; line-height: 1.5;">
                                Gestión Inteligente para Conjuntos Residenciales
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
