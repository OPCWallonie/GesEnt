<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chantier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'nom', 'description',
        'adresse_chantier', 'code_postal', 'ville', 'pays',
        'statut', 'avancement', 'date_debut', 'date_fin_prevue',
        'date_debut_reel', 'date_fin_reelle', 'notes', 'coefficient_marge',
    ];

    protected $casts = [
        'date_debut'        => 'date',
        'date_fin_prevue'   => 'date',
        'date_debut_reel'   => 'date',
        'date_fin_reelle'   => 'date',
        'avancement'        => 'integer',
        'coefficient_marge' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    public function bonsCommande()
    {
        return $this->hasMany(BonCommande::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function facturesAchat()
    {
        return $this->hasMany(FactureAchat::class);
    }

    public function totalVentes(): float
    {
        return (float) $this->factures()->whereIn('statut', ['en_attente', 'envoyee', 'payee'])->sum('montant_ttc');
    }

    public function totalAchats(): float
    {
        return (float) $this->facturesAchat()->sum('montant_ttc');
    }

    public function marge(): float
    {
        return $this->totalVentes() - $this->totalAchats();
    }

    public function tauxMarge(): ?float
    {
        $ventes = $this->totalVentes();
        return $ventes > 0 ? ($this->marge() / $ventes) * 100 : null;
    }

    /**
     * Coefficient de marge effectif : chantier > client > 0
     */
    public function coefficientMargeEffectif(): float
    {
        if ($this->coefficient_marge !== null && $this->coefficient_marge > 0) {
            return (float) $this->coefficient_marge;
        }
        return (float) ($this->client?->coefficient_marge ?? 0);
    }

    public function journal()
    {
        return $this->hasMany(JournalChantier::class)->orderByDesc('created_at');
    }
}
