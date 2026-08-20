<?php

namespace Domain\Exception;

final class OutOfStock extends DomainException
{
    public function __construct()
    {
        parent::__construct('The selected product is out of stock.');
    }
}
