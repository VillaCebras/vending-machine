<?php

namespace Domain\Service;

use Domain\ValueObject\Coin;

final class ChangeCalculator
{
    /**
     * @param Coin[] $availableCoins
     *
     * @return Coin[]
     */
    public function calculate(int $amountInCents, array $availableCoins): array
    {
        usort($availableCoins, static fn (Coin $left, Coin $right): int => $right->cents <=> $left->cents);

        return $this->findExactChange($amountInCents, $availableCoins, 0, []);
    }

    /**
     * @param Coin[] $coins
     * @param Coin[] $selected
     *
     * @return Coin[]
     */
    private function findExactChange(int $remaining, array $coins, int $index, array $selected): array
    {
        if (0 === $remaining) {
            return $selected;
        }
        if ($remaining < 0 || $index >= count($coins)) {
            return [];
        }

        $withCoin = $this->findExactChange($remaining - $coins[$index]->cents, $coins, $index + 1, [...$selected, $coins[$index]]);

        return [] !== $withCoin ? $withCoin : $this->findExactChange($remaining, $coins, $index + 1, $selected);
    }
}
