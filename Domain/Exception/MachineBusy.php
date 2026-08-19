<?php

namespace Domain\Exception;

final class MachineBusy extends DomainException
{
    public function __construct()
    {
        parent::__construct('The vending machine is already in use.');
    }
}
