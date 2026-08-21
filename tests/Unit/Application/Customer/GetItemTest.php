<?php

namespace Tests\Application\Customer;

use Application\Customer\GetItem\GetItem;
use Application\Customer\InsertMoney\InsertMoney;
use Application\Maintenance\AddItems\RestockOrder;
use Domain\Exception\CustomerModeRequired;
use Domain\Exception\InsufficientFunds;
use Domain\Exception\MachineBusy;
use Domain\Model\Customer;
use Domain\Model\Product;
use Domain\Service\ChangeCalculator;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class GetItemTest extends TestCase
{
    protected GetItem $useCase;
    protected InsertMoney $insertUseCase;
    protected InMemoryVendingMachineRepository $repository;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new GetItem($this->repository, new ChangeCalculator());
        $this->insertUseCase = new InsertMoney($this->repository);
        $this->customer = new Customer('customer-1');
    }

    public function testGetsItemWithExactAmount(): void
    {
        $this->addProduct(Product::fromName('WATER'));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(1.00));

        $result = $this->useCase->__invoke($this->customer, Product::fromName('WATER'));

        $this->assertSame([], $result);
    }

    public function testDecreasesStockWhenItemIsPurchased(): void
    {
        $this->addProduct(Product::fromName('WATER'));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(1.00));

        $this->assertSame(1, $this->repository->get()->stockOf(Product::fromName('WATER')));

        $this->useCase->__invoke($this->customer, Product::fromName('WATER'));

        $this->assertSame(0, $this->repository->get()->stockOf(Product::fromName('WATER')));
    }

    public function testFailsIfMachineTakenByOtherCustomer(): void
    {
        $this->insertUseCase->__invoke(new Customer('other-customer'), Coin::fromAmount(0.10));

        $this->expectException(MachineBusy::class);
        $this->useCase->__invoke($this->customer, Product::fromName('WATER'));
    }

    public function testFailsIfThereAreNotEnoughFunds(): void
    {
        $this->addProduct(Product::fromName('WATER'));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(0.25));

        $this->expectException(InsufficientFunds::class);
        $this->useCase->__invoke($this->customer, Product::fromName('WATER'));
    }

    public function testGetsItemAndReturnsChange(): void
    {
        $this->addProduct(Product::fromName('WATER'), [Coin::fromAmount(0.25)]);
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(1.00));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(0.25));

        $result = $this->useCase->__invoke($this->customer, Product::fromName('WATER'));

        $this->assertEquals([Coin::fromAmount(0.25)], $result);
    }

    public function testGetsItemWithoutReturningChangeWhenNoChangeIsAvailable(): void
    {
        $this->addProduct(Product::fromName('WATER'));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(1.00));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(0.25));

        $result = $this->useCase->__invoke($this->customer, Product::fromName('WATER'));

        $this->assertSame([], $result);
    }

    public function testFailsIfMachineIsInMaintenanceMode(): void
    {
        $machine = $this->repository->get();
        $machine->enableMaintenance('maintenance-code', 'maintenance-code');

        $this->expectException(CustomerModeRequired::class);
        $this->useCase->__invoke($this->customer, Product::fromName('WATER'));
    }

    /** @param Coin[] $changeCoins */
    private function addProduct(Product $product, array $changeCoins = []): void
    {
        $machine = $this->repository->get();
        $machine->enableMaintenance('maintenance-code', 'maintenance-code');
        $machine->addItems(new RestockOrder($product, 1));
        $machine->addChange($changeCoins);
        $machine->disableMaintenance();
    }
}
