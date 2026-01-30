<?php

namespace App\Mail;

use App\Models\Proveedores;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UafeDocumentosMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Proveedores $proveedor,
        public Collection $documentos,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Documentación UAFE requerida',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.uafe-documentos',
            with: [
                'proveedor' => $this->proveedor,
                'documentos' => $this->documentos,
            ],
        );
    }
}
