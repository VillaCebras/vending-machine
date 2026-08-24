<?php

namespace Symfony\Command;

use Application\Maintenance\AddChange\AddChange;
use Domain\ValueObject\Coin;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('Add change coins', 20)]
class AddChangeOperation implements MaintenanceOperation
{
    public function __construct(
        private readonly AddChange $addChange,
    ) {
    }

    public function hasPrompt(): bool
    {
        return true;
    }

    public function getPrompt(): string
    {
        return 'Coins (0.05,0.25,1.00)';
    }

    public function hasOutput(): bool
    {
        return true;
    }

    public function getOutput(): string
    {
        return '<info>Change coins added.</info>';
    }

    public function execute(?string $input): bool
    {
        $coins = array_map(
            fn (string $amount): Coin => Coin::fromAmount(trim($amount)),
            explode(',', (string) $input),
        );
        $this->addChange->execute($coins);

        return false;
    }
}
