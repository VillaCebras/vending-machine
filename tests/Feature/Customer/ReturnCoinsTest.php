<?php

namespace Tests\Feature\Customer;

use Tests\Feature\VendingMachineCommandTestCase;

class ReturnCoinsTest extends VendingMachineCommandTestCase
{
    public function testReturnsInsertedCoinsAndLeavesCustomerMode(): void
    {
        $display = $this->runCommand([
            '1',
            '1',
            '0.10',
            '1',
            '0.25',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Coins returned: 0.10 EUR, 0.25 EUR', $display);
        $this->assertSame(3, substr_count($display, 'Customer operation'));
        $this->assertFalse($this->repository->get()->isCustomerActive());
        $this->assertEquals(0, $this->repository->get()->insertedAmount());
    }

    public function testReturnsNoCoinsWhenNoneWereInserted(): void
    {
        $display = $this->runCommand([
            '1',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Coins returned: none', $display);
        $this->assertFalse($this->repository->get()->isCustomerActive());
    }
}
