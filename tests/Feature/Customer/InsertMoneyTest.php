<?php

namespace Tests\Feature\Customer;

use Tests\Feature\VendingMachineCommandTestCase;

class InsertMoneyTest extends VendingMachineCommandTestCase
{
    public function testInsertsACoinAndStaysInCustomerMode(): void
    {
        $display = $this->runCommand([
            '1',
            '1',
            '0.25',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Current balance: 0.25 EUR', $display);
        $this->assertSame(2, substr_count($display, 'Customer operation'));
        $this->assertStringContainsString('Coins returned:', $display);
        $this->assertFalse($this->repository->get()->isCustomerActive());
        $this->assertEquals(0, $this->repository->get()->insertedAmount());
    }

    public function testInsertsSeveralCoinsInTheSameSession(): void
    {
        $display = $this->runCommand([
            '1',
            '1',
            '0.10',
            '1',
            '0.25',
            '1',
            '1.00',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Current balance: 0.10 EUR', $display);
        $this->assertStringContainsString('Current balance: 0.35 EUR', $display);
        $this->assertStringContainsString('Current balance: 1.35 EUR', $display);
        $this->assertStringContainsString('Coins returned: 0.10 EUR, 0.25 EUR, 1.00 EUR', $display);
        $this->assertEquals(0, $this->repository->get()->insertedAmount());
    }

    public function testShowsAnErrorForAnInvalidCoinAndStaysInCustomerMode(): void
    {
        $display = $this->runCommand([
            '1',
            '1',
            '0.50',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Coin value is not accepted: 50 cents.', $display);
        $this->assertSame(2, substr_count($display, 'Customer operation'));
        $this->assertEquals(0, $this->repository->get()->insertedAmount());
    }
}
