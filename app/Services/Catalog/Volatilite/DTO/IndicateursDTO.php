<?php

namespace App\Services\Catalog\Volatilite\DTO;

final class IndicateursDTO
{
    public function __construct(
        public readonly int $nbChangements,
        public readonly ?float $prixMin,
        public readonly ?float $prixMax,
        public readonly ?float $prixMoyen,
        public readonly ?float $amplitudePct,
        public readonly ?float $positionRelative,
        public readonly ?float $tendance12mPct,
        public readonly ?float $r2Tendance,
        public readonly array $variationsRecentes3m,
        public readonly int $nbChangementsAnciens,
    ) {}

    public function suffisant(int $minChangements): bool
    {
        return $this->nbChangements >= $minChangements;
    }
}
