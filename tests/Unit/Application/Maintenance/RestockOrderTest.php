<?php

namespace Tests\Unit\Application\Maintenance;

use Domain\Model\Product;
use Application\Maintenance\AddItems\RestockOrder;
use PHPUnit\Framework\TestCase;

class RestockOrderTest extends TestCase
{
    public function testCreateRestockOrderSuccessfully(): void
    {
        $quantity = random_int(1, 100);
        $this->performQuantityTest($quantity);
    }

    public function testFailsWhenCreatingRestockOrderWithNegativeQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RestockOrder(Product::fromName('WATER'), -1);
    }

    public function testCreateZeroRestockOrderSuccessfully(): void
    {
        $this->performQuantityTest(0);
    }

    protected function performQuantityTest(int $quantity): void
    {
        $restockOrder = new RestockOrder(Product::fromName('WATER'), $quantity);
        $this->assertSame('WATER', $restockOrder->getProductName());
        $this->assertSame($quantity, $restockOrder->quantity);
    }
}
