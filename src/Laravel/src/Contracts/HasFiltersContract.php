<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts;

use MoonShine\Laravel\Collections\Fields;

interface HasFiltersContract
{
    public function hasFilters(): bool;

    public function getFilters(): Fields;
}
