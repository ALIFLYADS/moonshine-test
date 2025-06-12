<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Resource;

use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Laravel\QueryTags\QueryTag;

/**
 * @mixin CrudResourceContract
 * @deprecated Will be removed in 5.0
 * @see IndexPage
 */
trait HasQueryTags
{
    /**
     * @return list<QueryTag>
     */
    public function getQueryTags(): array
    {
        if($this->getIndexPage() !== null && $this->getIndexPage()->hasQueryTags()) {
            return $this->getIndexPage()->getQueryTags();
        }

        return $this->queryTags();
    }

    /**
     * Get an array of custom form actions
     *
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    public function hasQueryTags(): bool
    {
        return $this->queryTags() !== [] || $this->getIndexPage()?->hasQueryTags();
    }
}
