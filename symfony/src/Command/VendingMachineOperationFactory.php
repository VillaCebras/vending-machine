<?php

namespace Symfony\Command;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Service\ServiceCollectionInterface as ContainerInterface;

class VendingMachineOperationFactory
{
    public function __construct(
        #[AutowireLocator('vending_machine.operation')]
        private ContainerInterface $operations,
    ) {
    }

    public function create(string $choice): VendingMachineOperation
    {
        return $this->operations->get($choice);
    }

    /** @return list<string> */
    public function choices(): array
    {
        return array_keys($this->operations->getProvidedServices());
    }
}
