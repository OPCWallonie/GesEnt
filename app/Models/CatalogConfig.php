<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogConfig extends Model
{
    protected $table = 'catalog_config';

    protected $fillable = [
        'fournisseur', 'nom_affichage', 'actif',
        'url_api', 'identifiant', 'mot_de_passe', 'numero_client',
        'marge_defaut', 'derniere_sync', 'nb_produits', 'notes',
    ];

    protected $casts = [
        'actif'          => 'boolean',
        'marge_defaut'   => 'decimal:2',
        'derniere_sync'  => 'datetime',
        'nb_produits'    => 'integer',
    ];

    protected $hidden = ['mot_de_passe'];

    public function getMotDePasseDecrypteAttribute(): ?string
    {
        if (!$this->mot_de_passe) return null;
        try {
            return decrypt($this->mot_de_passe);
        } catch (\Exception) {
            return null;
        }
    }

    public function setMotDePasseAttribute(?string $value): void
    {
        $this->attributes['mot_de_passe'] = $value ? encrypt($value) : null;
    }
}
