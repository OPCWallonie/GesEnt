<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduitUsageStat extends Model
{
    protected $table = 'produit_usage_stats';

    protected $fillable = [
        'produit_id', 'catalog_produit_id',
        'nb_utilisations', 'nb_devis', 'derniere_utilisation', 'score',
    ];

    protected $casts = [
        'derniere_utilisation' => 'date',
        'score'                => 'decimal:2',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function catalogProduit()
    {
        return $this->belongsTo(CatalogProduit::class);
    }

    /**
     * Recalculer le score.
     * Formule : nb_utilisations * facteur_fraicheur
     * facteur_fraicheur = 1.0 si utilisé cette semaine, décroît jusqu'à 0.3 après 6 mois
     */
    public function recalculerScore(): void
    {
        $jours = $this->derniere_utilisation
            ? $this->derniere_utilisation->diffInDays(now())
            : 365;

        $facteur = max(0.3, 1.0 - (log1p($jours) / 10));
        $this->score = round($this->nb_utilisations * $facteur, 2);
        $this->save();
    }
}
