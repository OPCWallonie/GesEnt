<?php

namespace App\Notifications;

use App\Models\Devis;
use Illuminate\Notifications\Notification;

class DevisExpireBientot extends Notification
{
    public function __construct(public Devis $devis) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $jours = now()->diffInDays($this->devis->date_validite, false);
        return [
            'type'    => 'devis_expire',
            'titre'   => "Devis expire dans {$jours}j : {$this->devis->numero}",
            'message' => "Le devis {$this->devis->numero} ({$this->devis->client->nom}) expire le {$this->devis->date_validite->format('d/m/Y')}.",
            'url'     => route('devis.show', $this->devis),
            'montant' => $this->devis->montant_ttc,
        ];
    }
}
