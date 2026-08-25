<?php

namespace Symfony\Command;

use Application\Customer\InsertMoney\InsertMoney;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('Insert coin')]
class InsertCoinOperation implements VendingMachineOperation
{
    public function __construct(
        private readonly InsertMoney $insertMoney,
    ) {
    }

    public function hasPrompt(): bool
    {
        return true;
    }

    public function getPrompt(): string
    {
        return 'Coin amount (0.05, 0.10, 0.25 or 1.00): ';
    }

    public function hasOutput(): bool
    {
        return false;
    }

    public function getOutput(): string
    {
        return '';
    }

    public function execute(?string $input, ?Customer $customer): bool
    {
        $this->insertMoney->__invoke($customer, Coin::fromAmount((string) $input));

        return false;
    }
}
