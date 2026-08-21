<?php

namespace Application\Maintenance\AddItems;

use Domain\Repository\VendingMachineRepositoryInterface;
use Application\Maintenance\AddItems\RestockOrder;

final readonly class AddItems
{
    public function __construct(private VendingMachineRepositoryInterface $machines)
    {
    }

    /** @param RestockOrder[] $orders */
    public function execute(array $orders): void
    {
        $machine = $this->machines->get();
        foreach ($orders as $order) {
            $machine->addItems($order);
        }
        $this->machines->save($machine);
    }
}
