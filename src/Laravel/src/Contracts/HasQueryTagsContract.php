<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts;

use MoonShine\Laravel\QueryTags\QueryTag;

interface HasQueryTagsContract
{
    public function hasQueryTags(): bool;

    /**
     * @return list<QueryTag>
     */
    public function getQueryTags(): array;
}
