<?php

namespace Symfony\Command;

use Domain\Model\Customer;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('vending_machine.operation')]
interface VendingMachineOperation
{
    public function hasPrompt(): bool;
    public function getPrompt(): string;
    public function hasOutput(): bool;
    public function getOutput(): string;
    public function execute(?string $input, ?Customer $customer): bool;
}