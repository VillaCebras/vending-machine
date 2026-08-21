<?php

namespace Application\Maintenance\EnableService;

use Domain\Repository\VendingMachineRepositoryInterface;

final readonly class EnableService
{
    public function __construct(private VendingMachineRepositoryInterface $machines, private string $maintenanceCode)
    {
    }

    public function __invoke(string $code): void
    {
        $machine = $this->machines->get();
        $machine->enableMaintenance($code, $this->maintenanceCode);
        $this->machines->save($machine);
    }
}
