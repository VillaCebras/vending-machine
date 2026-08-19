<?php

namespace Tests\Application\Customer;

use Application\Customer\ReturnCoins\ReturnCoins;
use Application\Customer\InsertMoney\InsertMoney;
use Domain\Exception\CustomerModeRequired;
use Domain\Exception\MachineBusy;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class ReturnCoinsTest extends TestCase
{
    protected ReturnCoins $useCase;
    protected InsertMoney $insertUseCase;
    protected InMemoryVendingMachineRepository $repository;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new ReturnCoins($this->repository);
        $this->insertUseCase = new InsertMoney($this->repository);
        $this->customer = new Customer('customer-1');
    }

    public function testReturnsInsertedCoinsSuccessfully(): void
    {
        $coin1 = Coin::fromAmount(0.10);
        $coin2 = Coin::fromAmount(0.25);
        $machine = $this->repository->get();

        $this->insertUseCase->__invoke($this->customer, $coin1);
        $this->insertUseCase->__invoke($this->customer, $coin2);

        $coins = $this->useCase->__invoke($this->customer);

        $this->assertSame([$coin1, $coin2], $coins);
        $this->assertFalse($machine->isCustomerActive());
    }

    public function testReturnsEmptyArrayWhenNoCoinsAreInserted(): void
    {
        $machine = $this->repository->get();

        $coins = $this->useCase->__invoke($this->customer);

        $this->assertSame([], $coins);
    }

    public function testFailsIfMachineTakenByOtherCustomer(): void
    {
        $machine = $this->repository->get();
        $this->insertUseCase->__invoke(new Customer('other-customer'), Coin::fromAmount(0.10));

        $this->expectException(MachineBusy::class);
        $this->useCase->__invoke($this->customer);
    }
}