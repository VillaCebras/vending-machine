<?php

namespace Domain\Exception;

final class InsufficientFunds extends DomainException
{
    public function __construct()
    {
        parent::__construct('The inserted amount does not cover the product price.');
    }
}
