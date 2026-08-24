<?php

namespace Tests\Domain;

use Domain\Exception\InvalidProduct;
use Domain\Model\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testCreateProductSuccessfully(): void
    {
        $product = Product::fromName('WATER');
        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('WATER', $product->name);
        $this->assertSame(100, $product->priceInCents);
    }

    public function testCreateProductWithInvalidName(): void
    {
        $this->expectException(InvalidProduct::class);
        Product::fromName('INVALID');
    }
}
