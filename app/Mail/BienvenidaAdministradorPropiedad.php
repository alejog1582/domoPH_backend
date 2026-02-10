<?php

namespace App\Mail;

use App\Models\Propiedad;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaAdministradorPropiedad extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La propiedad creada
     */
    public Propiedad $propiedad;

    /**
     * El usuario administrador
     */
    public User $administrador;

    /**
     * La contraseña en texto plano (solo para el correo)
     */
    public string $password;

    /**
     * Create a new message instance.
     */
    public function __construct(Propiedad $propiedad, User $administrador, string $password)
    {
        $this->propiedad = $propiedad;
        $this->administrador = $administrador;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bienvenido a domoPH - Acceso a tu panel administrativo",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-administrador-propiedad',
            with: [
                'propiedad' => $this->propiedad,
                'administrador' => $this->administrador,
                'password' => $this->password,
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
