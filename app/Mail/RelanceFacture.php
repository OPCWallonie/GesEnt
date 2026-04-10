<?php

namespace App\Mail;

use App\Models\Facture;
use App\Models\ParametresEntreprise;
use App\Models\RelanceEtape;
use App\Services\DocumentService;
use App\Services\MailTemplateService;
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

    public string $signature;
    public ParametresEntreprise $parametres;
    public string $corpsEmail;

    public function __construct(
        public Facture $facture,
        public RelanceEtape $etape,
    ) {
        $p = ParametresEntreprise::instance();
        $this->parametres = $p;
        $this->signature  = $p->mail_signature ?? '';
        $this->corpsEmail = MailTemplateService::resoudreEtape($etape, $facture, 'corps_email');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: MailTemplateService::resoudreEtape($this->etape, $this->facture, 'sujet'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.relance');
    }

    public function attachments(): array
    {
        $facture    = $this->facture->loadMissing('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande');
        $parametres = $this->parametres;
        $totauxTva  = app(DocumentService::class)->calculerTotauxTva($facture->lignes);
        $attachments = [];

        // Toujours joindre la facture PDF
        $pdfFacture = Pdf::loadView('pdf.facture', compact('facture', 'parametres', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        $attachments[] = Attachment::fromData(
            fn() => $pdfFacture->output(),
            "facture-{$this->facture->numero}.pdf"
        )->withMime('application/pdf');

        // Joindre le courrier de relance si le canal l'exige
        if ($this->etape->avecCourrier()) {
            $etape = $this->etape;
            $pdfCourrier = Pdf::loadView('pdf.courrier-relance', compact('facture', 'parametres', 'etape'))
                ->setPaper('a4', 'portrait');

            $attachments[] = Attachment::fromData(
                fn() => $pdfCourrier->output(),
                "relance-{$this->facture->numero}.pdf"
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
