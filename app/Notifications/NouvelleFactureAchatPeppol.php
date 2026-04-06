<?php

namespace App\Notifications;

use App\Models\FactureAchat;
use Illuminate\Notifications\Notification;

class NouvelleFactureAchatPeppol extends Notification
{
    public function __construct(public FactureAchat $facture) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'facture_achat_peppol',
            'titre'   => 'Nouvelle facture reçue via Peppol',
            'message' => "Facture {$this->facture->reference_fournisseur} de {$this->facture->fournisseur->nom} — "
                . number_format((float) $this->facture->montant_ttc, 2, ',', ' ') . ' €. Vérifiez et assignez au chantier.',
            'url'     => route('factures-achat.show', $this->facture),
            'montant' => $this->facture->montant_ttc,
        ];
    }
}
