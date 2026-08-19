<?php

namespace Domain\Repository;

use Domain\Model\VendingMachine;

interface VendingMachineRepositoryInterface
{
    public function get(): VendingMachine;

    public function save(VendingMachine $machine): void;
}
