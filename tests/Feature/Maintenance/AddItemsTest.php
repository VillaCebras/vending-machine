<?php

namespace Tests\Feature\Maintenance;

use Domain\Model\Product;
use Tests\Feature\VendingMachineCommandTestCase;

class AddItemsTest extends VendingMachineCommandTestCase
{
    public function testAddsProductsAndStaysInMaintenanceMode(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '1',
            'WATER-3,JUICE-5',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Products added.', $display);
        $this->assertSame(2, substr_count($display, 'Maintenance operation'));
        $this->assertSame(3, $this->repository->get()->stockOf(Product::fromName('WATER')));
        $this->assertSame(5, $this->repository->get()->stockOf(Product::fromName('JUICE')));
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }

    public function testShowsAnErrorForANegativeQuantityAndStaysInMaintenanceMode(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '1',
            'WATER--1',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Quantity cannot be negative.', $display);
        $this->assertSame(0, $this->repository->get()->stockOf(Product::fromName('WATER')));
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }
}
