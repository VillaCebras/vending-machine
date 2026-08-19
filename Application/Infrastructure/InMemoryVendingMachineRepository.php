<?php

namespace Application\Infrastructure;

use Domain\Model\VendingMachine;
use Domain\Repository\VendingMachineRepositoryInterface;

final class InMemoryVendingMachineRepository implements VendingMachineRepositoryInterface
{
    public function __construct(private VendingMachine $machine = new VendingMachine())
    {
    }

    public function get(): VendingMachine
    {
        return $this->machine;
    }

    public function save(VendingMachine $machine): void
    {
        $this->machine = $machine;
    }
}
