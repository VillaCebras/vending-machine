<?php

namespace Tests\Feature\Maintenance;

use Tests\Feature\VendingMachineCommandTestCase;

class EnableServiceTest extends VendingMachineCommandTestCase
{
    public function testEnablesMaintenanceAndShowsTheMaintenanceMenu(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Maintenance mode activated.', $display);
        $this->assertSame(1, substr_count($display, 'Maintenance operation'));
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }

    public function testShowsAnErrorForAnInvalidCodeAndReturnsToTheModeSelector(): void
    {
        $display = $this->runCommand([
            '2',
            '0000',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('The maintenance code is invalid.', $display);
        $this->assertStringNotContainsString('Maintenance mode activated.', $display);
        $this->assertStringNotContainsString('Maintenance operation', $display);
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }
}
