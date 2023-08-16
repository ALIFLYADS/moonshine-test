<?php

declare(strict_types=1);

namespace MoonShine\Applies\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\ApplyContract;
use MoonShine\Fields\Field;

class RangeModelApply implements ApplyContract
{
    public function apply(Field $field): Closure
    {
        return static function (Builder $query) use ($field): Builder {
            $values = $field->requestValue();

            $query->when(
                $values['from'] ?? null,
                function ($query, $from) use ($field): void {
                    if ($field->attributes()->get('type') === 'date') {
                        $query->whereDate(
                            $field->column(),
                            '>=',
                            Carbon::parse($from)
                        );
                    } else {
                        $query->where($field->column(), '>=', $from);
                    }
                }
            )
                ->when(
                    $values['to'] ?? null,
                    function ($query, $to) use ($field): void {
                        if ($field->attributes()->get('type') === 'date') {
                            $query->whereDate(
                                $field->column(),
                                '<=',
                                Carbon::parse($to)
                            );
                        } else {
                            $query->where($field->column(), '<=', $to);
                        }
                    }
                );

            return $query;
        };
    }
}
