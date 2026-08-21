<?php

namespace Application\Customer\GetItem;

use Domain\Repository\VendingMachineRepositoryInterface;
use Domain\Service\ChangeCalculator;
use Domain\Model\Customer;
use Domain\Model\Product;

final readonly class GetItem
{
    public function __construct(private VendingMachineRepositoryInterface $machines, private ChangeCalculator $changeCalculator)
    {
    }

    /** @return Coin[] */
    public function __invoke(Customer $customer, Product $product): array
    {
        $machine = $this->machines->get();
        $result = $machine->buy($customer, $product, $this->changeCalculator);
        $this->machines->save($machine);

        return $result;
    }
}
