<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CatalogProduit extends Model
{
    protected $table = 'catalog_produits';

    protected $fillable = [
        'fournisseur', 'reference', 'designation', 'description',
        'unite', 'prix_catalogue', 'prix_revente', 'taux_tva',
        'categorie', 'sous_categorie', 'marque', 'ean',
        'en_stock', 'quantite_stock', 'delai_livraison',
        'donnees_brutes', 'derniere_sync',
    ];

    protected $casts = [
        'prix_catalogue'  => 'decimal:4',
        'prix_revente'    => 'decimal:4',
        'taux_tva'        => 'decimal:2',
        'en_stock'        => 'boolean',
        'donnees_brutes'  => 'array',
        'derniere_sync'   => 'datetime',
    ];

    // Fournisseurs pré-configurés (fallback si DB non disponible)
    public const FOURNISSEURS = [
        'desco'    => 'Desco',
        'vanmarke' => 'VanMarke',
        'wasco'    => 'Wasco',
        'ems'      => 'EMS',
        'autre'    => 'Autre',
    ];

    public function scopeSearch(Builder $q, string $terme): Builder
    {
        return $q->where(function ($q) use ($terme) {
            $q->where('designation', 'like', "%{$terme}%")
              ->orWhere('reference', 'like', "%{$terme}%")
              ->orWhere('marque', 'like', "%{$terme}%")
              ->orWhere('ean', 'like', "%{$terme}%");
        });
    }

    public function scopeFournisseur(Builder $q, ?string $fournisseur): Builder
    {
        return $fournisseur ? $q->where('fournisseur', $fournisseur) : $q;
    }

    public function getNomFournisseurAttribute(): string
    {
        // Cache statique pour éviter N+1 sur les listes paginées
        static $noms = null;
        if ($noms === null) {
            try {
                $noms = \App\Models\CatalogConfig::pluck('nom_affichage', 'fournisseur')->toArray();
            } catch (\Exception) {
                $noms = [];
            }
        }
        return $noms[$this->fournisseur]
            ?? self::FOURNISSEURS[$this->fournisseur]
            ?? ucwords(str_replace(['_', '-'], ' ', $this->fournisseur));
    }
}
