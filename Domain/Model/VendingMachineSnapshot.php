<?php

namespace Domain\Model;

final readonly class VendingMachineSnapshot
{
    /**
     * @param list<int>          $insertedCoinCents
     * @param list<int>          $changeCoinCents
     * @param array<string, int> $stock
     */
    public function __construct(
        public bool $maintenance,
        public ?string $customerId,
        public array $insertedCoinCents,
        public array $changeCoinCents,
        public array $stock,
    ) {
    }
}
