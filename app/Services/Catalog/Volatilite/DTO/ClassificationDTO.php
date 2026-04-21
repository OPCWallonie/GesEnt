<?php

namespace App\Services\Catalog\Volatilite\DTO;

final class ClassificationDTO
{
    public function __construct(
        public readonly string $classe,
        public readonly bool $signalRelatif,
        public readonly bool $signalAbsolu,
        public readonly string $groupeComparaison,
    ) {}
}
