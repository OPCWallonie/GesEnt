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

class FactureEnvoyee extends Mailable
{
    use Queueable, SerializesModels;

    public string $signature;
    public ParametresEntreprise $parametres;

    public function __construct(
        public Facture $facture,
        public string $messagePersonnalise = '',
    ) {
        $p = ParametresEntreprise::instance();
        $this->parametres = $p;
        $this->signature  = $p->mail_signature ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Facture {$this->facture->numero} — {$this->parametres->nom}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.facture');
    }

    public function attachments(): array
    {
        $this->facture->loadMissing('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande');
        $totauxTva = app(DocumentService::class)->calculerTotauxTva($this->facture->lignes);

        $pdf = Pdf::loadView('pdf.facture', [
            'facture'    => $this->facture,
            'parametres' => $this->parametres,
            'totauxTva'  => $totauxTva,
        ])->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                "facture-{$this->facture->numero}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
