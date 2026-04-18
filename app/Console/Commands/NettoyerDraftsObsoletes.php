<?php

namespace App\Console\Commands;

use App\Models\DocumentDraft;
use Illuminate\Console\Command;

class NettoyerDraftsObsoletes extends Command
{
    protected $signature   = 'gesent:nettoyer-drafts';
    protected $description = 'Supprime les drafts de documents plus vieux que 7 jours';

    public function handle(): int
    {
        $supprimes = DocumentDraft::where('saved_at', '<', now()->subDays(7))->delete();
        $this->info("{$supprimes} drafts obsolètes supprimés.");
        return self::SUCCESS;
    }
}
