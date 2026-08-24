<?php

namespace Symfony\Command;

use Application\Maintenance\DisableService\DisableService;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('Disable maintenance', 10)]
class DisableServiceOperation implements MaintenanceOperation
{
    public function __construct(
        private readonly DisableService $disableService,
    ) {
    }

    public function hasPrompt(): bool
    {
        return false;
    }

    public function getPrompt(): string
    {
        return '';
    }

    public function hasOutput(): bool
    {
        return true;
    }

    public function getOutput(): string
    {
        return '<info>Maintenance mode disabled.</info>';
    }

    public function execute(?string $input): bool
    {
        ($this->disableService)();

        return true;
    }
}
