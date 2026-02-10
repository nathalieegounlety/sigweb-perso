<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImportationPresentiel extends Mailable
{
    use Queueable, SerializesModels;
    public $contenu;
    public $mailSubject;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct($contenu)
    {
        $this->contenu = $contenu;
        $this->mailSubject = $contenu['subject'];
        $this->email = $contenu['email'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ERSUMA : '.$this->mailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email_importation_presentiel',
            with: [
                'contenu' => $this->contenu,
                'email' => $this->email,
            ]
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
