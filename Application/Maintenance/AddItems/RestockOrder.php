<?php

namespace Application\Maintenance\AddItems;

use Domain\Model\Product;

final readonly class RestockOrder
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }
    }

    public function getProductName(): string
    {
        return $this->product->name;
    }
}