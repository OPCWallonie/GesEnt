<?php

namespace App\Services\Catalog\Volatilite\DTO;

final class RecommandationIaDTO
{
    public function __construct(
        public readonly int    $catalogProduitId,
        public readonly string $designation,
        public readonly string $actionSuggeree,
        public readonly string $justification,
        public readonly float  $economieEstimeeEur,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            catalogProduitId:   (int)    ($data['catalog_produit_id']   ?? 0),
            designation:        (string) ($data['designation']           ?? ''),
            actionSuggeree:     (string) ($data['action_suggeree']       ?? 'aucune'),
            justification:      (string) ($data['justification']         ?? ''),
            economieEstimeeEur: (float)  ($data['economie_estimee_eur']  ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'catalog_produit_id'   => $this->catalogProduitId,
            'designation'          => $this->designation,
            'action_suggeree'      => $this->actionSuggeree,
            'justification'        => $this->justification,
            'economie_estimee_eur' => $this->economieEstimeeEur,
        ];
    }
}
