<?php

namespace Application\Maintenance\AddItems;

use Domain\Model\Product;

final readonly class RestockOrder
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {
    }
}