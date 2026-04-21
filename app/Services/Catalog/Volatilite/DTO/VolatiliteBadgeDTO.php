<?php

namespace App\Services\Catalog\Volatilite\DTO;

final class VolatiliteBadgeDTO
{
    public function __construct(
        public readonly string  $classe,
        public readonly ?string $niveau,      // 'warning' | 'opportunite' | 'info' | null
        public readonly ?string $icone,
        public readonly ?string $message,
        public readonly bool    $signalFort,
    ) {}

    public function visible(): bool
    {
        return $this->niveau !== null;
    }

    public function toArray(): array
    {
        return [
            'classe'      => $this->classe,
            'niveau'      => $this->niveau,
            'icone'       => $this->icone,
            'message'     => $this->message,
            'signal_fort' => $this->signalFort,
        ];
    }
}
