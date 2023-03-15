<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Commands;

interface CommandInterface
{
    public function run(): void;
}
