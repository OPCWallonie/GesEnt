<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chantier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'nom', 'reference', 'description',
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

    // ─── Référence chantier ───────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Chantier $chantier) {
            if (empty($chantier->reference)) {
                $chantier->reference = self::genererReference($chantier);
            }
        });
    }

    /**
     * Génère un code court unique.
     * Format : initiales du nom (2-3 lettres) + année + séquentiel
     * Exemple : "Villa Dubois" → "VD-2026-003"
     */
    public static function genererReference(Chantier $chantier): string
    {
        // Extraire les initiales du nom (premières lettres de chaque mot, max 3)
        $mots      = preg_split('/[\s\-_]+/', (string) $chantier->nom);
        $initiales = '';
        foreach (array_slice($mots, 0, 3) as $mot) {
            $lettre = mb_strtoupper(mb_substr(trim($mot), 0, 1));
            if (preg_match('/[A-Z0-9]/', $lettre)) {
                $initiales .= $lettre;
            }
        }
        if (strlen($initiales) < 2) {
            $initiales = mb_strtoupper(mb_substr((string) $chantier->nom, 0, 2));
        }

        $annee   = now()->format('Y');
        $prefixe = $initiales . '-' . $annee . '-';

        // Trouver le prochain séquentiel (ignorer l'enregistrement courant si modification)
        $dernier = static::withTrashed()
            ->where('reference', 'like', $prefixe . '%')
            ->when($chantier->exists, fn ($q) => $q->where('id', '!=', $chantier->id))
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $dernier ? ((int) substr($dernier, strlen($prefixe))) + 1 : 1;

        return $prefixe . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

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

    // ─── Main d'œuvre ─────────────────────────────────────────────
    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function coutMainOeuvre(?int $annee = null): float
    {
        $query = $this->pointages();
        if ($annee) {
            $query->whereYear('date', $annee);
        }
        return (float) $query->sum('cout_total');
    }

    public function margeReelle(?int $annee = null): float
    {
        return $this->totalVentes() - $this->totalAchats() - $this->coutMainOeuvre($annee);
    }

    public function tauxMargeReelle(?int $annee = null): ?float
    {
        $ventes = $this->totalVentes();
        return $ventes > 0 ? ($this->margeReelle($annee) / $ventes) * 100 : null;
    }
}
