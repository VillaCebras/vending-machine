<?php

namespace Tests\Unit\Application\Maintenance;

use Application\Maintenance\EnableService\EnableService;
use Domain\Exception\InvalidMaintenanceCode;
use Domain\Exception\MachineBusy;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class EnableServiceTest extends TestCase
{
    protected EnableService $useCase;
    protected InMemoryVendingMachineRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new EnableService($this->repository, 'maintenance-code');
    }

    public function testEnablesMaintenanceSuccessfully(): void
    {
        $this->useCase->__invoke('maintenance-code');

        $this->assertTrue($this->repository->get()->isInMaintenance());
    }

    public function testFailsIfMachineIsBeingUsedByACustomer(): void
    {
        $machine = $this->repository->get();
        $machine->insertCoin(Coin::fromAmount(0.10), new Customer('customer-1'));

        $this->expectException(MachineBusy::class);

        $this->useCase->__invoke('maintenance-code');
    }

    public function testFailsWithInvalidMaintenanceCode(): void
    {
        $this->expectException(InvalidMaintenanceCode::class);

        $this->useCase->__invoke('invalid-code');
    }
}
