<?php

namespace App\Models;

use App\States\Facture\FactureStatut;
use App\States\Facture\Payee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Spatie\ModelStates\HasStates;

class Facture extends Model
{
    use SoftDeletes, HasStates;

    protected $fillable = [
        'numero', 'bon_commande_id', 'client_id', 'chantier_id',
        'mode_paiement_id', 'created_by',
        'statut', 'date_document', 'date_echeance',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'frais_port', 'ristourne_globale', 'acompte_deduit',
        'retenue_garantie_pct', 'retenue_garantie_montant', 'retenue_garantie_liberee_at',
        'montant_net_a_payer', 'delai_reglement',
        'date_paiement', 'montant_paye', 'montant_total_paye', 'notes',
        'nb_relances', 'derniere_relance_at', 'prochaine_relance_at', 'relance_auto',
        'numero_situation', 'pourcentage_avancement', 'pourcentage_cumule', 'montant_anterieur',
        'peppol_reference', 'peppol_envoye_at',
        'odoo_move_id', 'odoo_synced_at',
    ];

    protected $casts = [
        'statut'                       => FactureStatut::class,
        'date_document'                => 'date',
        'date_echeance'                => 'date',
        'date_paiement'                => 'date',
        'derniere_relance_at'          => 'date',
        'retenue_garantie_liberee_at'  => 'date',
        'montant_ht'                   => 'decimal:4',
        'montant_tva'                  => 'decimal:4',
        'montant_ttc'                  => 'decimal:4',
        'frais_port'                   => 'decimal:4',
        'ristourne_globale'            => 'decimal:2',
        'acompte_deduit'               => 'decimal:4',
        'retenue_garantie_pct'         => 'decimal:2',
        'retenue_garantie_montant'     => 'decimal:4',
        'montant_net_a_payer'          => 'decimal:4',
        'montant_paye'                 => 'decimal:4',
        'montant_total_paye'           => 'decimal:4',
        'nb_relances'                  => 'integer',
        'prochaine_relance_at'         => 'date',
        'relance_auto'                 => 'boolean',
        'numero_situation'             => 'integer',
        'pourcentage_avancement'       => 'decimal:2',
        'pourcentage_cumule'           => 'decimal:2',
        'montant_anterieur'            => 'decimal:4',
        'peppol_envoye_at'             => 'datetime',
        'odoo_synced_at'               => 'datetime',
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class);
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

    public function lignes()
    {
        return $this->morphMany(LigneDocument::class, 'documentable')->orderBy('ordre');
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance
            && $this->date_echeance->isPast()
            && in_array((string) $this->statut, ['en_attente', 'envoyee']);
    }

    public function resteAPayer(): float
    {
        return max(0, $this->montant_net_a_payer - $this->montant_paye);
    }

    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->montant_net_a_payer - $this->montant_total_paye);
    }

    public function getEstTotalementPayeeAttribute(): bool
    {
        return $this->montant_total_paye >= $this->montant_net_a_payer;
    }

    public function recalculerPaiements(): void
    {
        $total           = $this->paiements()->sum('montant');
        $dernierPaiement = $this->paiements()->latest('date_paiement')->first();

        $this->update([
            'montant_total_paye' => $total,
            'montant_paye'       => $total,
            'date_paiement'      => $dernierPaiement?->date_paiement,
        ]);

        if ($total >= $this->montant_net_a_payer && !($this->statut instanceof Payee)) {
            try {
                $this->statut->transitionTo(Payee::class);
            } catch (TransitionNotFound $e) {
                Log::warning("Facture {$this->numero} : paiement complet mais transition vers payée impossible depuis {$this->statut}.");
            }
        }
    }

    public function enregistrerRelance(): void
    {
        $this->increment('nb_relances');
        $this->update([
            'derniere_relance_at'  => now()->toDateString(),
            'prochaine_relance_at' => now()->addDays(14)->toDateString(),
        ]);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class)->orderBy('date_paiement');
    }

    public function avoirs()
    {
        return $this->hasMany(\App\Models\Avoir::class);
    }

    public function emailEnvois()
    {
        return $this->morphMany(EmailEnvoi::class, 'document')->orderByDesc('envoye_at');
    }

    public function totalAvoirs(): float
    {
        return (float) $this->avoirs()->sum('montant_ttc');
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
