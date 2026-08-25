<?php

namespace Symfony\Command;

use Application\Customer\ReturnCoins\ReturnCoins;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('Return coins')]
class ReturnCoinsOperation implements VendingMachineOperation
{
    public function __construct(
        private readonly ReturnCoins $returnCoins,
        /** @var Coin[] */
        private array $coins = [],
    ) {
    }

    public function hasPrompt(): bool
    {
        return false;
    }

    public function getPrompt(): string
    {
        return '';
    }

    public function hasOutput(): bool
    {
        return true;
    }

    public function getOutput(): string
    {
        return sprintf('Coins returned: %s', $this->formatCoins($this->coins));
    }

    public function execute(?string $input, ?Customer $customer): bool
    {
        $this->coins = ($this->returnCoins)($customer);

        return true;
    }

    /** @param Coin[] $coins */
    private function formatCoins(array $coins): string
    {
        return [] === $coins ? 'none' : implode(', ', array_map(static fn (Coin $coin): string => $coin->amount().' EUR', $coins));
    }
}
