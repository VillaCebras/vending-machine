<?php

namespace Domain\Model;

use Domain\Exception\InvalidProduct;

final readonly class Product
{
    public function __construct(
        public string $name,
        public int $priceInCents,
    ) {
    }

    public static function fromName(string $name): self
    {
        $normalizedName = strtoupper(trim($name));

        return match ($normalizedName) {
            'WATER' => new self('WATER', 100),
            'SODA' => new self('SODA', 125),
            'JUICE' => new self('JUICE', 150),
            default => throw new InvalidProduct($name),
        };
    }
}
