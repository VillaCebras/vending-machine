<?php

namespace Tests\Application\Maintenance;

use Application\Maintenance\AddItems\AddItems;
use Application\Maintenance\AddItems\RestockOrder;
use Domain\Exception\MaintenanceModeRequired;
use Domain\Model\Customer;
use Domain\Model\Product;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class AddItemsTest extends TestCase
{
    protected AddItems $useCase;
    protected InMemoryVendingMachineRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new AddItems($this->repository);
    }

    public function testAddsOneItemSuccessfully(): void
    {
        $this->enableMaintenance();

        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), 1)]);

        $this->assertSame(1, $this->repository->get()->stockOf(Product::fromName('WATER')));
    }

    public function testAddsMultipleItemsSuccessfully(): void
    {
        $this->enableMaintenance();

        $this->useCase->execute([
            new RestockOrder(Product::fromName('WATER'), 3),
            new RestockOrder(Product::fromName('SODA'), 2),
            new RestockOrder(Product::fromName('JUICE'), 5),
        ]);

        $machine = $this->repository->get();
        $this->assertSame(3, $machine->stockOf(Product::fromName('WATER')));
        $this->assertSame(2, $machine->stockOf(Product::fromName('SODA')));
        $this->assertSame(5, $machine->stockOf(Product::fromName('JUICE')));
    }

    public function testAddsZeroItemsSuccessfully(): void
    {
        $this->enableMaintenance();

        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), 0)]);

        $this->assertSame(0, $this->repository->get()->stockOf(Product::fromName('WATER')));
    }

    public function testFailsWhenAddingAnItemWithNegativeQuantity(): void
    {
        $this->enableMaintenance();
        $this->expectException(\InvalidArgumentException::class);

        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), -1)]);
    }

    public function testAddsTheSameItemTwiceSuccessfully(): void
    {
        $this->enableMaintenance();
        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), 1)]);

        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), 1)]);

        $this->assertSame(2, $this->repository->get()->stockOf(Product::fromName('WATER')));
    }

    public function testFailsIfMachineIsNotInMaintenanceMode(): void
    {
        $this->expectException(MaintenanceModeRequired::class);

        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), 1)]);
    }

    public function testFailsIfMachineHasAnActiveCustomer(): void
    {
        $machine = $this->repository->get();
        $machine->insertCoin(Coin::fromAmount(0.10), new Customer('customer-1'));

        $this->expectException(MaintenanceModeRequired::class);

        $this->useCase->execute([new RestockOrder(Product::fromName('WATER'), 1)]);
    }

    private function enableMaintenance(): void
    {
        $this->repository->get()->enableMaintenance('maintenance-code', 'maintenance-code');
    }
}
