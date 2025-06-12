<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Applies\FieldsWithoutFilters;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Exceptions\FilterException;
use Throwable;

/**
 * @mixin CrudPageContract
 */
trait HasFilters
{
    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [];
    }

    public function hasFilters(): bool
    {
        return $this->filters() !== [];
    }

    /**
     * @throws Throwable
     */
    public function getFilters(): Fields
    {
        $filters = Fields::make($this->filters())
            ->withoutOutside()
            ->wrapNames('filter');

        $filters->each(static function ($filter): void {
            if (\in_array($filter::class, FieldsWithoutFilters::LIST)) {
                throw FilterException::notAcceptable($filter::class);
            }
        });

        return $filters;
    }
}
