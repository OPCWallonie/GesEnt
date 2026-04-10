<?php

namespace App\Mail;

use App\Models\BonCommande;
use App\Models\ParametresEntreprise;
use App\Services\DocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BonCommandeEnvoye extends Mailable
{
    use Queueable, SerializesModels;

    public string $signature;
    public ParametresEntreprise $parametres;

    public function __construct(
        public BonCommande $bonCommande,
        public string $messagePersonnalise = '',
    ) {
        $p = ParametresEntreprise::instance();
        $this->parametres = $p;
        $this->signature  = $p->mail_signature ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bon de commande {$this->bonCommande->numero} — {$this->parametres->nom}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.bon-commande');
    }

    public function attachments(): array
    {
        $this->bonCommande->loadMissing('client', 'chantier', 'modePaiement', 'lignes');
        $totauxTva = app(DocumentService::class)->calculerTotauxTva($this->bonCommande->lignes);

        $pdf = Pdf::loadView('pdf.bon-commande', [
            'bonCommande' => $this->bonCommande,
            'parametres'  => $this->parametres,
            'totauxTva'   => $totauxTva,
        ])->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                "bdc-{$this->bonCommande->numero}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
