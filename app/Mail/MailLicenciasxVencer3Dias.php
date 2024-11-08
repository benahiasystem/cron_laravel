<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class MailLicenciasxVencer3Dias extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected array $licencias,
        protected string $nombres,
        protected string $email,
        protected string $almacen
    )
    { }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('no-responder@vendty.net', 'Vendty POS y Tienda Virtual'),
            subject: 'URGENTE tú servicio Vendty vencerá en 3 días',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'LicenciasxVencer3Dias',
            with: [
                'licencias' => $this->licencias,
                'nombres'   => $this->nombres,
                'email'     => $this->email,
                'almacen'   => $this->almacen
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
