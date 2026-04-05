<?php

namespace App\Notifications;

use App\Models\JournalChantier;
use Illuminate\Notifications\Notification;

class NouveauJournalChantier extends Notification
{
    public function __construct(public JournalChantier $entree) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = JournalChantier::TYPES[$this->entree->type] ?? ['label' => 'Événement'];
        return [
            'type'    => 'journal_chantier',
            'titre'   => "{$type['label']} — {$this->entree->chantier->nom}",
            'message' => $this->entree->titre ?? $this->entree->contenu ?? "Nouvelle entrée sur le chantier.",
            'url'     => route('chantiers.show', $this->entree->chantier_id),
            'auteur'  => $this->entree->user->name,
        ];
    }
}
