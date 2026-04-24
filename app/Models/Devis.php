<?php

namespace App\Models;

use App\States\Devis\DevisStatut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class Devis extends Model
{
    use SoftDeletes, HasStates;

    protected $fillable = [
        'numero', 'client_id', 'chantier_id', 'mode_paiement_id', 'created_by',
        'statut', 'date_document', 'date_validite',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'frais_port', 'ristourne_globale', 'acompte', 'delai_reglement',
        'notes', 'date_statut',
    ];

    protected $casts = [
        'statut'          => DevisStatut::class,
        'date_document'   => 'date',
        'date_validite'   => 'date',
        'date_statut'     => 'date',
        'montant_ht'      => 'decimal:4',
        'montant_tva'     => 'decimal:4',
        'montant_ttc'     => 'decimal:4',
        'frais_port'      => 'decimal:4',
        'ristourne_globale' => 'decimal:2',
        'acompte'         => 'decimal:4',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function lignes()
    {
        return $this->morphMany(LigneDocument::class, 'documentable')->orderBy('ordre');
    }

    public function bonCommande()
    {
        return $this->hasOne(BonCommande::class);
    }

    public function analyseIa(): HasOne
    {
        return $this->hasOne(DevisAnalyseIa::class);
    }

    public function emailEnvois()
    {
        return $this->morphMany(EmailEnvoi::class, 'document')->orderByDesc('envoye_at');
    }

    public function estExpire(): bool
    {
        return $this->date_validite
            && $this->date_validite->isPast()
            && ! in_array((string) $this->statut, ['valide', 'archive', 'refuse']);
    }

    public function scopeSansArchives($query)
    {
        return $query->where('statut', '!=', 'archive');
    }

    public function scopeUniquementArchives($query)
    {
        return $query->where('statut', 'archive');
    }

    public function peutEtreModifie(): bool
    {
        return (string) $this->statut !== 'archive';
    }

    public function peutEtreSupprime(): bool
    {
        return (string) $this->statut !== 'archive' && $this->bonCommande === null;
    }

    public function peutEtreArchive(): bool
    {
        return (string) $this->statut !== 'archive';
    }

    /**
     * Ventilation TVA : ['21' => ['ht' => 1000.00, 'tva' => 210.00], ...]
     */
    public function totauxParTva(): array
    {
        $totaux = [];
        foreach ($this->lignes->where('est_section', false) as $ligne) {
            $taux = (string)(float) $ligne->taux_tva;
            $ht   = (float) $ligne->montant_ht;
            $totaux[$taux] ??= ['ht' => 0.0, 'tva' => 0.0];
            $totaux[$taux]['ht']  += $ht;
            $totaux[$taux]['tva'] += $ht * ((float) $ligne->taux_tva / 100);
        }
        ksort($totaux);
        return $totaux;
    }
}
