<?php

namespace Tests\Application\Customer;

use Application\Customer\GetBalance\GetBalance;
use Application\Customer\InsertMoney\InsertMoney;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;

class GetBalanceTest extends TestCase
{
    protected GetBalance $useCase;
    protected InsertMoney $insertUseCase;
    protected InMemoryVendingMachineRepository $repository;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
        $this->useCase = new GetBalance($this->repository);
        $this->insertUseCase = new InsertMoney($this->repository);
        $this->customer = new Customer('customer-1');
    }

    public function testReturnsZeroWhenNoCoinsAreInserted(): void
    {
        $this->assertSame(0, $this->useCase->__invoke());
    }

    public function testReturnsInsertedAmountInCents(): void
    {
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(0.10));
        $this->insertUseCase->__invoke($this->customer, Coin::fromAmount(0.25));

        $this->assertSame(35, $this->useCase->__invoke());
    }
}
