<?php

namespace App\Notifications;

use App\Models\Facture;
use Illuminate\Notifications\Notification;

class FactureEnRetard extends Notification
{
    public function __construct(public Facture $facture) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'facture_en_retard',
            'titre'   => "Facture en retard : {$this->facture->numero}",
            'message' => "La facture {$this->facture->numero} ({$this->facture->client->nom}) est en retard de paiement. Échéance : {$this->facture->date_echeance->format('d/m/Y')}.",
            'url'     => route('factures.show', $this->facture),
            'montant' => $this->facture->montant_net_a_payer,
        ];
    }
}
