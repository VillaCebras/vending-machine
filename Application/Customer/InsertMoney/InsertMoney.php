<?php

namespace Application\Customer\InsertMoney;

use Domain\Repository\VendingMachineRepositoryInterface;
use Domain\Model\Customer;
use Domain\ValueObject\Coin;

final readonly class InsertMoney
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    public function __invoke(Customer $customer, Coin $coin): float
    {
        $machine = $this->machines->get();
        if (!$machine->isCustomerActive()) {
            $machine->enterCustomer($customer);
        } elseif (!$machine->isCustomer($customer)) {
            throw new \Domain\Exception\MachineBusy();
        }
        $total = $machine->insertCoin($coin);
        $this->machines->save($machine);

        return $total / 100;
    }
}
