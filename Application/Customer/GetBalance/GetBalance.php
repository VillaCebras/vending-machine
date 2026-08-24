<?php

namespace Application\Customer\GetBalance;

use Domain\Repository\VendingMachineRepositoryInterface;

final readonly class GetBalance
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    public function __invoke(): int
    {
        $machine = $this->machines->get();
        return $machine->insertedAmount();
    }
}
