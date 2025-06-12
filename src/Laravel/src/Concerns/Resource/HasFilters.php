<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Resource;

use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Applies\FieldsWithoutFilters;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Contracts\HasFiltersContract;
use MoonShine\Laravel\Exceptions\FilterException;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use Throwable;

/**
 * @mixin CrudResourceContract
 * @deprecated Will be removed in 5.0
 * @see IndexPage
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
        if($this->filters() !== []) {
            return true;
        }
        return $this->getIndexPage() instanceof HasFiltersContract && $this->getIndexPage()->hasFilters();
    }

    /**
     * @throws Throwable
     */
    public function getFilters(): Fields
    {
        if($this->getIndexPage() instanceof HasFiltersContract && $this->getIndexPage()->hasFilters()) {
            return $this->getIndexPage()->getFilters();
        }

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
