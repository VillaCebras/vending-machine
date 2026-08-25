<?php

namespace Symfony\Command;

use Domain\Model\Customer;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('vending_machine.operation')]
interface VendingMachineOperation extends CommandOperation
{
    public function execute(?string $input, ?Customer $customer): bool;
}
