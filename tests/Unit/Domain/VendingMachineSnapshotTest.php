<?php

namespace Tests\Domain;

use Application\Maintenance\AddItems\RestockOrder;
use Domain\Model\Customer;
use Domain\Model\Product;
use Domain\Model\VendingMachine;
use Domain\ValueObject\Coin;
use PHPUnit\Framework\TestCase;

class VendingMachineSnapshotTest extends TestCase
{
    public function testRestoresMachineState(): void
    {
        $customer = new Customer('customer-1');
        $machine = new VendingMachine();
        $machine->enableMaintenance('code', 'code');
        $machine->addChange([Coin::fromCents(25), Coin::fromCents(10)]);
        $machine->addItems(new RestockOrder(Product::fromName('WATER'), 3));
        $machine->disableMaintenance();
        $machine->insertCoin(Coin::fromCents(100), $customer);

        $restored = VendingMachine::fromSnapshot($machine->snapshot());

        $this->assertFalse($restored->isInMaintenance());
        $this->assertTrue($restored->isCustomer($customer));
        $this->assertSame(100, $restored->insertedAmount());
        $this->assertSame(2, $restored->availableChange());
        $this->assertSame(3, $restored->stockOf(Product::fromName('WATER')));
    }
}
