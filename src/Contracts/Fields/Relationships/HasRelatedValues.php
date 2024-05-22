<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Fields\Relationships;

use Closure;
use MoonShine\DTOs\Select\Options;

interface HasRelatedValues
{
    public function getValues(): Options;

    public function setValues(array $values): void;

    public function valuesQuery(Closure $callback): static;
}
