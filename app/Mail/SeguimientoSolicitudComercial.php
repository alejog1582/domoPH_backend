<?php

namespace App\Mail;

use App\Models\SolicitudSeguimiento;
use App\Models\SolicitudComercial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SeguimientoSolicitudComercial extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * El seguimiento creado
     */
    public SolicitudSeguimiento $seguimiento;

    /**
     * La solicitud comercial asociada
     */
    public SolicitudComercial $solicitud;

    /**
     * Create a new message instance.
     */
    public function __construct(SolicitudSeguimiento $seguimiento)
    {
        $this->seguimiento = $seguimiento->load('usuario');
        $this->solicitud = $seguimiento->solicitudComercial->load('archivos');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Actualización sobre tu solicitud - domoPH",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.seguimiento-solicitud-comercial',
            with: [
                'seguimiento' => $this->seguimiento,
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
