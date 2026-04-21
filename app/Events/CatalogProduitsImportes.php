<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CatalogProduitsImportes
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly array $produitIds,
        public readonly string $source,
    ) {}
}
