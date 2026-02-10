<?php

namespace App\Mail;

use App\Models\Mascota;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MascotaRegistrada extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La mascota registrada
     */
    public Mascota $mascota;

    /**
     * Create a new message instance.
     */
    public function __construct(Mascota $mascota)
    {
        $this->mascota = $mascota->load(['residente.user', 'unidad.propiedad']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Mascota registrada en domoPH - {$this->mascota->nombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mascota-registrada',
            with: [
                'mascota' => $this->mascota,
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
