<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Laravel\QueryTags\QueryTag;

/**
 * @mixin CrudPageContract
 */
trait HasQueryTags
{
    /**
     * @return list<QueryTag>
     */
    public function getQueryTags(): array
    {
        return $this->queryTags();
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    public function hasQueryTags(): bool
    {
        return $this->queryTags() !== [];
    }
}
