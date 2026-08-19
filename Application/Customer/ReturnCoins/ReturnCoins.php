<?php

namespace Application\Customer\ReturnCoins;

use Domain\Exception\MachineBusy;
use Domain\Model\Customer;
use Domain\Repository\VendingMachineRepositoryInterface;
use Domain\ValueObject\Coin;

final readonly class ReturnCoins
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    /** @return Coin[] */
    public function __invoke(Customer $customer): array
    {
        $machine = $this->machines->get();
        $coins = $machine->returnCoins($customer);
        $this->machines->save($machine);

        return $coins;
    }
}
