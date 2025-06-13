<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Resource;

use Illuminate\Contracts\Database\Eloquent\Builder;

interface WithQueryBuilderContract
{
    public function hasWith(): bool;

    public function newQuery(): Builder;

    public function getQuery(): Builder;

    public function customQueryBuilder(Builder $builder): static;

    public function isDisabledQueryFeatures(): bool;

    public function disableQueryFeatures(): static;

    public function disableSaveQueryState(): static;

    public function getSortColumn(): string;

    public function getSortDirection(): string;

    public function setPaginatorPage(?int $page): static;

    public function isPaginationUsed(): bool;
}
