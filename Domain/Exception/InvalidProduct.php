<?php

namespace Domain\Exception;

final class InvalidProduct extends DomainException
{
    public function __construct(string $product)
    {
        parent::__construct(sprintf('Product is not available: %s.', $product));
    }
}
