<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactureAchat extends Model
{
    use SoftDeletes;

    protected $table = 'factures_achat';

    protected $fillable = [
        'numero', 'fournisseur_id', 'chantier_id', 'bon_commande_id', 'created_by',
        'reference_fournisseur', 'categorie',
        'date_document', 'date_echeance',
        'montant_ht', 'taux_tva', 'montant_tva', 'montant_ttc',
        'statut', 'date_paiement', 'notes',
        'peppol_id', 'peppol_sender_id', 'peppol_recu_at', 'peppol_source', 'peppol_raw_data',
        'odoo_move_id', 'odoo_synced_at',
    ];

    protected $casts = [
        'date_document'  => 'date',
        'date_echeance'  => 'date',
        'date_paiement'  => 'date',
        'montant_ht'     => 'decimal:2',
        'taux_tva'       => 'decimal:2',
        'montant_tva'    => 'decimal:2',
        'montant_ttc'    => 'decimal:2',
        'peppol_recu_at'  => 'datetime',
        'peppol_raw_data' => 'array',
        'odoo_synced_at'  => 'datetime',
    ];

    public static array $categories = [
        'materiel'       => 'Matériel',
        'sous_traitance' => 'Sous-traitance',
        'frais_generaux' => 'Frais généraux',
        'divers'         => 'Divers',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class);
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance
            && $this->date_echeance->isPast()
            && $this->statut === 'en_attente';
    }

    public function getLabelCategorieAttribute(): string
    {
        return self::$categories[$this->categorie] ?? $this->categorie;
    }
}
