<?php

declare(strict_types=1);

namespace MoonShine\Fields;

use Illuminate\Support\Collection;
use JsonException;
use MoonShine\Contracts\Fields\DefaultValueTypes\DefaultCanBeArray;
use MoonShine\Contracts\Fields\DefaultValueTypes\DefaultCanBeNumeric;
use MoonShine\Contracts\Fields\DefaultValueTypes\DefaultCanBeString;
use MoonShine\Contracts\Fields\HasDefaultValue;
use MoonShine\Traits\Fields\CanBeMultiple;
use MoonShine\Traits\Fields\Searchable;
use MoonShine\Traits\Fields\SelectTrait;
use MoonShine\Traits\Fields\WithDefaultValue;

class Select extends Field implements
    HasDefaultValue,
    DefaultCanBeArray,
    DefaultCanBeString,
    DefaultCanBeNumeric
{
    use CanBeMultiple;
    use Searchable;
    use SelectTrait;
    use WithDefaultValue;

    protected string $view = 'moonshine::fields.select';

    /**
     * @throws JsonException
     */
    protected function resolvePreview(): string
    {
        $value = $this->toFormattedValue();

        if ($this->isMultiple()) {
            $value = is_string($value) ?
                json_decode($value, true, 512, JSON_THROW_ON_ERROR)
                : $value;

            return collect($value)
                ->when(
                    ! $this->isRawMode(),
                    fn ($collect): Collection => $collect->map(
                        fn ($v): string => (string) data_get($this->flattenValues(), $v, '')
                    )
                )
                ->implode(',');
        }

        return (string) data_get($this->flattenValues(), $value, '');
    }
}
