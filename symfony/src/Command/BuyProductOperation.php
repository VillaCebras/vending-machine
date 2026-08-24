<?php

namespace Symfony\Command;

use Application\Customer\GetItem\GetItem;
use Domain\Model\Customer;
use Domain\Model\Product;
use Domain\ValueObject\Coin;
use Symfony\Command\VendingMachineOperation;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('Buy product')]
class BuyProductOperation implements VendingMachineOperation
{
    public function __construct(
        private readonly GetItem $getItem,
        /** @var Coin[] */
        private array $change = [],
    ) {}

    public function hasPrompt(): bool
    {
        return true;
    }

    public function getPrompt(): string
    {
        return 'Product (WATER, SODA or JUICE): ';
    }

    public function hasOutput(): bool
    {
        return true;
    }

    public function getOutput(): string
    {
        return sprintf('Product served. Change: %s', $this->formatCoins($this->change));
    }

    public function execute(?string $input, ?Customer $customer): bool
    {
        $product = Product::fromName((string) $input);
        $this->change = ($this->getItem)($customer, $product);
        return true;
    }

    /** @param Coin[] $coins */
    private function formatCoins(array $coins): string
    {
        return [] === $coins ? 'none' : implode(', ', array_map(static fn (Coin $coin): string => $coin->amount().' EUR', $coins));
    }
}