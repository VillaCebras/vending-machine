<?php

namespace Tests\Feature\Maintenance;

use Tests\Feature\VendingMachineCommandTestCase;

class DisableServiceTest extends VendingMachineCommandTestCase
{
    public function testDisablesMaintenanceAndReturnsToTheModeSelector(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '3',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Maintenance mode disabled.', $display);
        $this->assertSame(1, substr_count($display, 'Maintenance operation'));
        $this->assertStringContainsString('Choose an operation:', $display);
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }

    public function testAfterDisableEnteringMaintenanceAsksForTheCodeAgain(): void
    {
        $display = $this->runCommand([
            '2',
            '1234',
            '3',
            '2',
            '0000',
            'q',
        ])->getDisplay();

        $this->assertStringContainsString('Maintenance mode disabled.', $display);
        $this->assertStringContainsString('The maintenance code is invalid.', $display);
        $this->assertFalse($this->repository->get()->isInMaintenance());
    }
}
