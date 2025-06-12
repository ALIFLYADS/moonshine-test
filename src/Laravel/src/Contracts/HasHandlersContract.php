<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts;

use MoonShine\Laravel\Handlers\Handlers;

interface HasHandlersContract
{
    public function hasHandlers(): bool;

    public function getHandlers(): Handlers;
}
