<?php

namespace Tests\Feature\Maintenance;

use Tests\Feature\VendingMachineCommandTestCase;

class AddChangeTest extends VendingMachineCommandTestCase
{
    public function testAddsChangeCoinsAndStaysInMaintenanceMode(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '2',
            '0.05,0.25,1.00',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Change coins added.', $display);
        $this->assertSame(2, substr_count($display, 'Maintenance operation'));
        $this->assertSame(3, $this->repository->get()->availableChange());
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }

    public function testShowsAnErrorForAnInvalidCoinAndStaysInMaintenanceMode(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '2',
            '0.50',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Coin value is not accepted: 50 cents.', $display);
        $this->assertSame(0, $this->repository->get()->availableChange());
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }
}
