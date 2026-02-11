<?php

namespace App\Mail;

use App\Models\CuentaCobro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuentaCobroGenerada extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La cuenta de cobro generada
     */
    public CuentaCobro $cuentaCobro;

    /**
     * Create a new message instance.
     */
    public function __construct(CuentaCobro $cuentaCobro)
    {
        $this->cuentaCobro = $cuentaCobro->load(['unidad.propiedad', 'detalles']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $periodo = $this->cuentaCobro->periodo;
        return new Envelope(
            subject: "Nueva cuenta de cobro - Período {$periodo}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cuenta-cobro-generada',
            with: [
                'cuentaCobro' => $this->cuentaCobro,
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
