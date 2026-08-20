<?php

namespace Tests\Application\Customer;

use Application\Customer\InsertMoney\InsertMoney;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class InsertMoneyTest extends TestCase
{
    protected InsertMoney $useCase;
    protected InMemoryVendingMachineRepository $repository;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new InsertMoney($this->repository);
        $this->customer = new Customer('customer-1');
    }

    public function testInsertCoinSuccessfully()
    {
        $coin = Coin::fromAmount(0.25);
        $totalInserted = $this->useCase->__invoke($this->customer, $coin);

        $this->assertIsFloat($totalInserted);
        $this->assertEquals(0.25, $totalInserted);
        $this->assertSame($this->customer, $this->repository->get()->customer());
    }

    public function testInsertMultipleCoinsSuccessfully()
    {
        $coin1 = Coin::fromAmount(0.10);
        $coin2 = Coin::fromAmount(0.25);
        $coin3 = Coin::fromAmount(1.00);

        $totalInserted1 = $this->useCase->__invoke($this->customer, $coin1);
        $totalInserted2 = $this->useCase->__invoke($this->customer, $coin2);
        $totalInserted3 = $this->useCase->__invoke($this->customer, $coin3);

        $this->assertEquals(0.10, $totalInserted1);
        $this->assertEquals(0.35, $totalInserted2);
        $this->assertEquals(1.35, $totalInserted3);
    }

    public function testTakesFreeMachine()
    {
        $machine = $this->repository->get();

        $coin = Coin::fromAmount(0.25);
        $totalInserted = $this->useCase->__invoke($this->customer, $coin);

        $this->assertTrue($machine->isCustomerActive());
        $this->assertSame($this->customer->id, $machine->customer()->id);
    }

    public function testFailsIfMachineTakenByOtherCustomer()
    {
        $coin1 = Coin::fromAmount(0.10);

        $this->useCase->__invoke(new Customer('other-customer'), $coin1);

        $this->expectException(\Domain\Exception\MachineBusy::class);
        $this->useCase->__invoke($this->customer, $coin1);
    }

    public function testFailsIfMachineInMaintenanceMode()
    {
        $coin1 = Coin::fromAmount(0.10);

        $machine = $this->repository->get();
        $machine->enableMaintenance('maintenance-code', 'maintenance-code');

        $this->expectException(\Domain\Exception\CustomerModeRequired::class);
        $this->useCase->__invoke($this->customer, $coin1);
    }
}
