<?php

namespace App\Services\Catalog\Volatilite\DTO;

final class AnalyseIaDevisDTO
{
    /**
     * @param RecommandationIaDTO[] $recommandations
     */
    public function __construct(
        public readonly string $synthese,
        public readonly string $niveauAlerte,
        public readonly array  $recommandations,
    ) {}

    public static function fromArray(array $data): self
    {
        $recos = array_map(
            fn($r) => RecommandationIaDTO::fromArray($r),
            $data['recommandations'] ?? []
        );

        return new self(
            synthese:        (string) ($data['synthese']      ?? ''),
            niveauAlerte:    (string) ($data['niveau_alerte'] ?? 'info'),
            recommandations: $recos,
        );
    }

    public function toArray(): array
    {
        return [
            'synthese'        => $this->synthese,
            'niveau_alerte'   => $this->niveauAlerte,
            'recommandations' => array_map(fn($r) => $r->toArray(), $this->recommandations),
        ];
    }

    public function economieTotale(): float
    {
        return array_sum(array_map(fn($r) => $r->economieEstimeeEur, $this->recommandations));
    }
}
