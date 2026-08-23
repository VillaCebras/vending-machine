<?php

namespace Tests\Feature\Customer;

use Application\Maintenance\AddItems\RestockOrder;
use Domain\Model\Product;
use Domain\ValueObject\Coin;
use Tests\Feature\VendingMachineCommandTestCase;

class GetItemTest extends VendingMachineCommandTestCase
{
    public function testBuysAProductWithExactAmount(): void
    {
        $this->stock(Product::fromName('WATER'));

        $display = $this->runCommand([
            '1',
            '1',
            '1.00',
            '2',
            'WATER',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Product served. Change: none', $display);
        $this->assertSame(0, $this->repository->get()->stockOf(Product::fromName('WATER')));
        $this->assertFalse($this->repository->get()->isCustomerActive());
    }

    public function testBuysAProductAndReturnsChange(): void
    {
        $this->stock(Product::fromName('WATER'), [Coin::fromAmount(0.25)]);

        $display = $this->runCommand([
            '1',
            '1',
            '1.00',
            '1',
            '0.25',
            '2',
            'WATER',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Product served. Change: 0.25 EUR', $display);
        $this->assertSame(0, $this->repository->get()->stockOf(Product::fromName('WATER')));
    }

    public function testShowsAnErrorWhenThereAreNotEnoughFundsAndStaysInCustomerMode(): void
    {
        $this->stock(Product::fromName('WATER'));

        $display = $this->runCommand([
            '1',
            '1',
            '0.25',
            '2',
            'WATER',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('The inserted amount does not cover the product price.', $display);
        $this->assertSame(3, substr_count($display, 'Customer operation'));
        $this->assertSame(1, $this->repository->get()->stockOf(Product::fromName('WATER')));
    }

    /** @param Coin[] $changeCoins */
    private function stock(Product $product, array $changeCoins = []): void
    {
        $machine = $this->repository->get();
        $machine->enableMaintenance('1234', '1234');
        $machine->addItems(new RestockOrder($product, 1));
        $machine->addChange($changeCoins);
        $machine->disableMaintenance();
    }
}
