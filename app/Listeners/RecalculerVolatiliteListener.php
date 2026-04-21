<?php

namespace App\Listeners;

use App\Events\CatalogProduitsImportes;
use App\Models\CatalogProduit;
use App\Services\Catalog\Volatilite\VolatiliteService;

class RecalculerVolatiliteListener
{
    // PAS de ShouldQueue en v1 (Infomaniak mutualisé sans queue worker fiable)
    // À ajouter quand on basculera sur serveur dédié

    public function __construct(private VolatiliteService $service) {}

    public function handle(CatalogProduitsImportes $event): void
    {
        try {
            $produits = CatalogProduit::whereIn('id', $event->produitIds)->get();
            $this->service->recalculerProduits($produits);
        } catch (\Throwable $e) {
            \Log::error('Erreur recalcul volatilité après import', [
                'source'      => $event->source,
                'nb_produits' => count($event->produitIds),
                'exception'   => $e->getMessage(),
            ]);
            // Ne PAS rethrow : un import doit toujours réussir même si la volatilité plante
        }
    }
}
