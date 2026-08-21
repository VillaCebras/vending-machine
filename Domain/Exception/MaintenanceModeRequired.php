<?php

namespace Domain\Exception;

final class MaintenanceModeRequired extends DomainException
{
    public function __construct()
    {
        parent::__construct('The vending machine must be in maintenance mode.');
    }
}
