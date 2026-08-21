<?php

namespace Application\Maintenance\DisableService;

use Domain\Repository\VendingMachineRepositoryInterface;

final readonly class DisableService
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    public function __invoke(): void
    {
        $machine = $this->machines->get();
        $machine->disableMaintenance();
        $this->machines->save($machine);
    }
}
