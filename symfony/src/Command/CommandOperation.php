<?php

namespace Symfony\Command;

interface CommandOperation
{
    public function hasPrompt(): bool;

    public function getPrompt(): string;

    public function hasOutput(): bool;

    public function getOutput(): string;
}
