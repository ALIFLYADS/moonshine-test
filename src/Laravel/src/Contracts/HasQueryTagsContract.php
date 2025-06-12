<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts;

interface HasQueryTagsContract
{
    public function hasQueryTags(): bool;

    public function getQueryTags(): array;
}
