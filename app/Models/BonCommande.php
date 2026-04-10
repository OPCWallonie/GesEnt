<?php

namespace App\Models;

use App\States\BonCommande\BdcStatut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class BonCommande extends Model
{
    use SoftDeletes, HasStates;

    protected $table = 'bons_commande';

    protected $fillable = [
        'numero', 'devis_id', 'client_id', 'chantier_id', 'mode_paiement_id', 'created_by',
        'statut', 'date_document', 'date_debut_travaux', 'date_fin_prevue',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'frais_port', 'ristourne_globale', 'acompte', 'delai_reglement',
        'notes', 'date_statut',
    ];

    protected $casts = [
        'statut'            => BdcStatut::class,
        'date_document'     => 'date',
        'date_debut_travaux' => 'date',
        'date_fin_prevue'   => 'date',
        'date_statut'       => 'date',
        'montant_ht'        => 'decimal:4',
        'montant_tva'       => 'decimal:4',
        'montant_ttc'       => 'decimal:4',
        'frais_port'        => 'decimal:4',
        'ristourne_globale' => 'decimal:2',
        'acompte'           => 'decimal:4',
    ];

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

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

    public function avenants()
    {
        return $this->hasMany(Avenant::class)->orderBy('numero_ordre');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class)->orderBy('numero_situation');
    }

    // Accesseur rétrocompatible : retourne la dernière facture (ou null)
    public function getFactureAttribute(): ?Facture
    {
        return $this->factures->last();
    }

    public function lignes()
    {
        return $this->morphMany(LigneDocument::class, 'documentable')->orderBy('ordre');
    }

    // Toutes les lignes : BDC + avenants regroupés pour facturation
    public function toutesLesLignes()
    {
        $lignesBdc = $this->lignes;
        $lignesAvenants = $this->avenants->flatMap->lignes;
        return $lignesBdc->concat($lignesAvenants);
    }

    public function montantTotalAvecAvenants(): array
    {
        $avenants = $this->avenants;
        return [
            'ht'  => $this->montant_ht + $avenants->sum('montant_ht'),
            'tva' => $this->montant_tva + $avenants->sum('montant_tva'),
            'ttc' => $this->montant_ttc + $avenants->sum('montant_ttc'),
            'frais_port' => $this->frais_port + $avenants->sum('frais_port'),
            'acompte'    => $this->acompte + $avenants->sum('acompte'),
        ];
    }

    public function pourcentageFacture(): float
    {
        return (float) $this->factures()
            ->whereIn('statut', ['en_attente', 'envoyee', 'payee', 'en_retard'])
            ->sum('pourcentage_avancement');
    }

    public function pourcentageRestant(): float
    {
        return max(0, 100 - $this->pourcentageFacture());
    }

    public function montantFacture(): float
    {
        return (float) $this->factures()
            ->whereIn('statut', ['en_attente', 'envoyee', 'payee', 'en_retard'])
            ->sum('montant_ttc');
    }

    public function montantRestant(): float
    {
        $totaux = $this->montantTotalAvecAvenants();
        return max(0, $totaux['ttc'] - $this->montantFacture());
    }

    public function peutEtreFacture(): bool
    {
        if (!in_array((string) $this->statut, ['valide', 'en_cours'])) {
            return false;
        }
        if (!$this->avenants->every(fn($a) => $a->statut === 'valide')) {
            return false;
        }
        return $this->pourcentageRestant() > 0;
    }

    public function prochainNumeroSituation(): int
    {
        return (int) $this->factures()->max('numero_situation') + 1;
    }

    /**
     * Ventilation TVA : ['21' => ['ht' => 1000.00, 'tva' => 210.00], ...]
     * Inclut les lignes BDC uniquement (sans avenants).
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
