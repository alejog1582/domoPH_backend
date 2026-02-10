<?php

namespace App\Mail;

use App\Models\SolicitudComercial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaSolicitudComercial extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La solicitud comercial
     */
    public SolicitudComercial $solicitud;

    /**
     * Create a new message instance.
     */
    public function __construct(SolicitudComercial $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tipoSolicitud = match($this->solicitud->tipo_solicitud) {
            'cotizacion' => 'Cotización',
            'demo' => 'Demo',
            'contacto' => 'Contacto',
            default => 'Solicitud',
        };

        return new Envelope(
            subject: "Nueva Solicitud de {$tipoSolicitud} - {$this->solicitud->nombre_contacto}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-comercial',
            with: [
                'solicitud' => $this->solicitud,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
