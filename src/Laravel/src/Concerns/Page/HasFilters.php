<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Applies\FieldsWithoutFilters;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Contracts\Page\IndexPageContract;
use MoonShine\Laravel\Exceptions\FilterException;
use Throwable;

/**
 * @mixin IndexPageContract
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

    /**
     * @return array<array-key, mixed>
     */
    public function getFilterParams(): array
    {
        return $this->getResource()->getFilterParams();
    }
}
