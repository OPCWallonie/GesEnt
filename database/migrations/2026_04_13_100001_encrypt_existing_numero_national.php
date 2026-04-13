<?php

use App\Models\Ouvrier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Lire les valeurs en clair AVANT que le cast 'encrypted' entre en jeu,
        // puis les réécrire via le modèle pour déclencher le chiffrement.
        Ouvrier::withTrashed()
            ->whereNotNull('numero_national')
            ->each(function (Ouvrier $ouvrier) {
                // Lire la valeur brute en base (pas encore chiffrée par le cast)
                $raw = DB::table('ouvriers')->where('id', $ouvrier->id)->value('numero_national');
                if (! $raw) {
                    return;
                }
                // Si la valeur commence par 'eyJ' c'est déjà du JSON chiffré (Laravel encrypted cast)
                if (str_starts_with($raw, 'eyJ')) {
                    return;
                }
                // Réécrire via le modèle — le cast encrypted chiffre automatiquement
                $ouvrier->numero_national = $raw;
                $ouvrier->saveQuietly();
            });
    }

    public function down(): void
    {
        // Irréversible par conception — les données sensibles ne doivent pas
        // être stockées en clair. Le down() ne déchiffre pas.
    }
};
