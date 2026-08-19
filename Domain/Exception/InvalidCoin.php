<?php

namespace Domain\Exception;

final class InvalidCoin extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(sprintf('Coin value is not accepted: %d cents.', $cents));
    }
}
