<?php

namespace Domain\ValueObject;

use Domain\Exception\InvalidProduct;

enum Product: string
{
    case WATER = 'WATER';
    case SODA = 'SODA';
    case JUICE = 'JUICE';

    public function priceInCents(): int
    {
        return match ($this) {
            self::WATER => 100,
            self::SODA => 125,
            self::JUICE => 150,
        };
    }

    public static function fromName(string $name): self
    {
        return self::tryFrom(strtoupper(trim($name))) ?? throw new InvalidProduct($name);
    }
}
