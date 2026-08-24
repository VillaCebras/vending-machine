<?php

namespace Symfony\Command;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Service\ServiceCollectionInterface as ContainerInterface;

class MaintenanceOperationFactory
{
    public function __construct(
        #[AutowireLocator('vending_machine.maintenance_operation')]
        private ContainerInterface $operations,
    ) {
    }

    public function create(string $choice): MaintenanceOperation
    {
        return $this->operations->get($choice);
    }

    /** @return list<string> */
    public function choices(): array
    {
        return array_keys($this->operations->getProvidedServices());
    }
}
