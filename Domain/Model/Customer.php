<?php

namespace Domain\Model;

final readonly class Customer
{
    public function __construct(public readonly string $id)
    {
    }
}
