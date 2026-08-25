<?php

namespace Symfony\Command;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('vending_machine.maintenance_operation')]
interface MaintenanceOperation extends CommandOperation
{
    public function execute(?string $input): bool;
}
