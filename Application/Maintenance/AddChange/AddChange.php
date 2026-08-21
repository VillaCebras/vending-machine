<?php

namespace Application\Maintenance\AddChange;

use Domain\Repository\VendingMachineRepositoryInterface;
use Domain\ValueObject\Coin;

final readonly class AddChange
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    /** @param Coin[] $coins */
    public function execute(array $coins): void
    {
        $machine = $this->machines->get();
        $machine->addChange($coins);
        $this->machines->save($machine);
    }
}
