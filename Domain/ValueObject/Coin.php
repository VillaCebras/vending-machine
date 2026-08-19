<?php

namespace Domain\ValueObject;

use Domain\Exception\InvalidCoin;

final readonly class Coin
{
    private const VALID_VALUES = [5, 10, 25, 100];

    private function __construct(public int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if (!in_array($cents, self::VALID_VALUES, true)) {
            throw new InvalidCoin($cents);
        }

        return new self($cents);
    }

    public static function fromAmount(string|int|float $amount): self
    {
        $normalized = number_format((float) $amount, 2, '.', '');

        return self::fromCents((int) round((float) $normalized * 100));
    }

    public function amount(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }
}
