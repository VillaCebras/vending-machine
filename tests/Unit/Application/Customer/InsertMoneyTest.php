<?php

namespace Tests\Application\Customer;

use PHPUnit\Framework\TestCase;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Application\Customer\InsertMoney\InsertMoney;
use Application\Infrastructure\InMemoryVendingMachineRepository;

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

    public function test_insert_coin_successfully()
    {
        $coin = Coin::fromAmount(0.25);
        $totalInserted = $this->useCase->__invoke($this->customer, $coin);

        $this->assertIsFloat($totalInserted);
        $this->assertEquals(0.25, $totalInserted);
        $this->assertSame($this->customer, $this->repository->get()->customer());
    }

    public function test_insert_multiple_coins_successfully()
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

    public function test_takes_free_machine()
    {
        $machine = $this->repository->get();
        
        $coin = Coin::fromAmount(0.25);
        $totalInserted = $this->useCase->__invoke($this->customer, $coin);
        
        $this->assertTrue($machine->isCustomerActive());
        $this->assertSame($this->customer->id, $machine->customer()->id);
    }

    public function test_fails_if_machine_taken_by_other_customer()
    {
        $coin1 = Coin::fromAmount(0.10);

        $machine = $this->repository->get();
        $machine->enterCustomer(new Customer('other-customer'));

        $this->expectException(\Domain\Exception\MachineBusy::class);
        $this->useCase->__invoke($this->customer, $coin1);
    }

    public function test_fails_if_machine_in_maintenance_mode()
    {
        $coin1 = Coin::fromAmount(0.10);

        $machine = $this->repository->get();
        $machine->enableMaintenance('maintenance-code', 'maintenance-code');

        $this->expectException(\Domain\Exception\MachineBusy::class);
        $this->useCase->__invoke($this->customer, $coin1);
    }
}