<?php

namespace App\Mail;

use App\Models\Devis;
use App\Models\ParametresEntreprise;
use App\Http\Controllers\DevisController;
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

    public function __construct(
        public Devis $devis,
        public string $messagePersonnalise = '',
    ) {
        $this->signature = ParametresEntreprise::instance()->mail_signature ?? '';
    }

    public function envelope(): Envelope
    {
        $parametres = ParametresEntreprise::instance();
        return new Envelope(
            subject: "Devis {$this->devis->numero} — {$parametres->nom}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.devis');
    }

    public function attachments(): array
    {
        $this->devis->loadMissing('client', 'chantier', 'modePaiement', 'lignes');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = DevisController::calculerTotauxTva($this->devis->lignes);

        $pdf = Pdf::loadView('pdf.devis', [
            'devis'      => $this->devis,
            'parametres' => $parametres,
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
