<?php

namespace Domain\Exception;

final class InvalidMaintenanceCode extends DomainException
{
    public function __construct()
    {
        parent::__construct('The maintenance code is invalid.');
    }
}
