<?php

namespace App\Mail;

use App\Models\Facture;
use App\Models\ParametresEntreprise;
use App\Services\DocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RelanceFacture extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Facture $facture,
        public int $niveauRelance, // 1, 2, 3+
    ) {}

    public function envelope(): Envelope
    {
        $sujet = match (true) {
            $this->niveauRelance === 1 => "Rappel — Facture {$this->facture->numero}",
            $this->niveauRelance === 2 => "2ème rappel — Facture {$this->facture->numero} en retard",
            default                    => "URGENT — Facture {$this->facture->numero} impayée",
        };

        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.relance-facture');
    }

    public function attachments(): array
    {
        $facture    = $this->facture->loadMissing('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = app(DocumentService::class)->calculerTotauxTva($facture->lignes);

        $pdf = Pdf::loadView('pdf.facture', compact('facture', 'parametres', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                "facture-{$this->facture->numero}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
