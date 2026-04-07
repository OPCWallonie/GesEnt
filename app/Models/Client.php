<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom', 'statut_juridique', 'adresse', 'code_postal', 'ville', 'pays',
        'telephone', 'fax', 'gsm', 'email', 'site_web',
        'numero_tva', 'numero_affiliation', 'code_client',
        'notes', 'actif', 'coefficient_marge',
        'odoo_partner_id', 'odoo_synced_at',
    ];

    protected $casts = [
        'actif'             => 'boolean',
        'coefficient_marge' => 'decimal:2',
        'odoo_synced_at'    => 'datetime',
    ];

    public function chantiers()
    {
        return $this->hasMany(Chantier::class);
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

    public function getNomCompletAttribute(): string
    {
        return $this->statut_juridique
            ? "{$this->nom} ({$this->statut_juridique})"
            : $this->nom;
    }
}
