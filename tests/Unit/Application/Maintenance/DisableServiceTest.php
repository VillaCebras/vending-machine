<?php

namespace Tests\Unit\Application\Maintenance;

use Application\Maintenance\DisableService\DisableService;
use Domain\Exception\MaintenanceModeRequired;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class DisableServiceTest extends TestCase
{
    protected DisableService $useCase;
    protected InMemoryVendingMachineRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new DisableService($this->repository);
    }

    public function testDisablesMaintenanceSuccessfullyAfterEnable(): void
    {
        $machine = $this->repository->get();
        $machine->enableMaintenance('maintenance-code', 'maintenance-code');

        $this->useCase->__invoke();

        $this->assertFalse($machine->isInMaintenance());
    }

    public function testFailsIfMaintenanceWasNotEnabledBefore(): void
    {
        $this->expectException(MaintenanceModeRequired::class);

        $this->useCase->__invoke();
    }
}
