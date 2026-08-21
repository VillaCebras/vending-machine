<?php

namespace Tests\Application\Maintenance;

use Application\Maintenance\AddChange\AddChange;
use Domain\Exception\MaintenanceModeRequired;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class AddChangeTest extends TestCase
{
    protected AddChange $useCase;
    protected InMemoryVendingMachineRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new AddChange($this->repository);
    }

    public function testAddsChangeSuccessfully(): void
    {
        $machine = $this->repository->get();
        $machine->enableMaintenance('maintenance-code', 'maintenance-code');

        $this->useCase->execute([Coin::fromAmount(0.10), Coin::fromAmount(0.25), Coin::fromAmount(1.00) ]);

        $this->assertSame(3, $machine->availableChange());
    }

    public function testFailsIfMachineIsNotInMaintenanceMode(): void
    {
        $this->expectException(MaintenanceModeRequired::class);

        $this->useCase->execute([Coin::fromAmount(0.25)]);
    }

    public function testFailsIfMachineHasAnActiveCustomer(): void
    {
        $machine = $this->repository->get();
        $machine->insertCoin(Coin::fromAmount(0.10), new Customer('customer-1'));

        $this->expectException(MaintenanceModeRequired::class);

        $this->useCase->execute([Coin::fromAmount(0.25)]);
    }
}
