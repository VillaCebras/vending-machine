<?php

namespace Domain\Exception;

final class CustomerModeRequired extends DomainException
{
    public function __construct()
    {
        parent::__construct('A customer session is required.');
    }
}
