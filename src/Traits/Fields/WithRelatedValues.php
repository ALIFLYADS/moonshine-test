<?php

declare(strict_types=1);

namespace MoonShine\Traits\Fields;

use Closure;
use Illuminate\Support\Facades\DB;

trait WithRelatedValues
{
    protected array $values = [];

    protected ?Closure $valuesQuery = null;

    public function valuesQuery(Closure $callback): self
    {
        $this->valuesQuery = $callback;

        return $this;
    }

    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    protected function resolveValuesQuery()
    {
        if (! empty($this->values)) {
            return $this->values;
        }

        $relation = $this->getRelation();

        if (is_null($relation)) {
            return $this->values;
        }

        $related = $relation->getRelated();
        $query = $related->newModelQuery();

        if (is_callable($this->valuesQuery)) {
            $query = call_user_func($this->valuesQuery, $query);
        }

        return $query;
    }

    public function values(): array
    {
        $query = $this->resolveValuesQuery();
        $related = $query->getModel();

        if (is_callable($this->formattedValueCallback())) {
            $values = $query->get()
                ->mapWithKeys(
                    fn ($item): array => [
                        $item->getKey() => call_user_func(
                            $this->formattedValueCallback(),
                            $item
                        ),
                    ]
                );
        } else {
            $table = DB::getTablePrefix() . $related->getTable();
            $key = "$table.{$related->getKeyName()}";
            $column = "$table.{$this->getResourceColumn()}";

            $values = $query->selectRaw("$key, $column")
                ->pluck($this->getResourceColumn(), $related->getKeyName());
        }

        return $values->toArray();
    }
}
