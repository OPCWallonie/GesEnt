<?php

namespace App\Mail;

use App\Models\Devis;
use App\Models\ParametresEntreprise;
use App\Services\DocumentService;
use App\Services\MailConfigService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DevisEnvoye extends Mailable
{
    use Queueable, SerializesModels;

    public string $signature;
    public ParametresEntreprise $parametres;

    public function __construct(
        public Devis $devis,
        public string $messagePersonnalise = '',
    ) {
        $p = ParametresEntreprise::instance();
        $this->parametres = $p;
        $this->signature  = $p->mail_signature ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Devis {$this->devis->numero} — {$this->parametres->nom}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.devis');
    }

    public function attachments(): array
    {
        $this->devis->loadMissing('client', 'chantier', 'modePaiement', 'lignes');
        $totauxTva = app(DocumentService::class)->calculerTotauxTva($this->devis->lignes);

        $pdf = Pdf::loadView('pdf.devis', [
            'devis'      => $this->devis,
            'parametres' => $this->parametres,
            'totauxTva'  => $totauxTva,
        ])->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                "devis-{$this->devis->numero}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
