<?php

namespace Application\Customer\InsertMoney;

use Domain\Model\Customer;
use Domain\Repository\VendingMachineRepositoryInterface;
use Domain\ValueObject\Coin;

final readonly class InsertMoney
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    public function __invoke(Customer $customer, Coin $coin): float
    {
        $machine = $this->machines->get();
        $machine->insertCoin($coin, $customer);
        $this->machines->save($machine);

        return $machine->insertedAmount() / 100;
    }
}
