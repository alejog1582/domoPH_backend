<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
    /**
     * Enviar mensaje de WhatsApp
     * 
     * Por ahora, esta función genera un enlace de WhatsApp y lo registra en logs.
     * Puede ser extendida para usar una API real como Twilio, WhatsApp Business API, etc.
     * 
     * @param string $telefono Número de teléfono (formato: 573123456789)
     * @param string $mensaje Mensaje a enviar
     * @return bool
     */
    public static function enviarMensaje(string $telefono, string $mensaje): bool
    {
        try {
            // Limpiar el número de teléfono (remover espacios, guiones, paréntesis, etc.)
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
            
            // Si el número no empieza con código de país, asumir que es Colombia (57)
            if (!str_starts_with($telefonoLimpio, '57') && strlen($telefonoLimpio) == 10) {
                $telefonoLimpio = '57' . $telefonoLimpio;
            }
            
            // Generar enlace de WhatsApp
            $mensajeCodificado = urlencode($mensaje);
            $enlaceWhatsApp = "https://wa.me/{$telefonoLimpio}?text={$mensajeCodificado}";
            
            // Por ahora, registrar en logs
            // En producción, aquí se podría integrar con una API de WhatsApp Business
            // Ejemplo: Twilio, WhatsApp Business API, etc.
            Log::info('Mensaje de WhatsApp generado', [
                'telefono' => $telefonoLimpio,
                'mensaje' => $mensaje,
                'enlace' => $enlaceWhatsApp,
            ]);
            
            // TODO: Integrar con API de WhatsApp Business
            // Ejemplo con Twilio:
            // $client = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            // $client->messages->create(
            //     "whatsapp:+{$telefonoLimpio}",
            //     ['from' => 'whatsapp:' . env('TWILIO_WHATSAPP_NUMBER'), 'body' => $mensaje]
            // );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje de WhatsApp: ' . $e->getMessage(), [
                'telefono' => $telefono,
                'mensaje' => $mensaje,
            ]);
            return false;
        }
    }
}
