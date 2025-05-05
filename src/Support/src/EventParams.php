<?php

declare(strict_types=1);

namespace MoonShine\Support;

use InvalidArgumentException;
use MoonShine\Support\Traits\Makeable;

/**
 * @method static static make(array $data = [])
 */
class EventParams
{
    use Makeable;

    private int $delay = 0;

    /**
     * @param  array<string, numeric|string>  $data
     */
    public function __construct(private array $data = []) {}

    /**
     * @param  array<non-empty-string, scalar>  $data
     */
    public function selectors(array $data): static
    {
        if(array_filter($data) === []) {
            return $this;
        }

        $this->data['selectors'] = $this->transform($data);

        return $this;
    }

    /**
     * @param  array<non-empty-string, scalar>  $data
     */
    public function fieldsValues(array $data): static
    {
        if(array_filter($data) === []) {
            return $this;
        }

        $this->data['fields_values'] = $this->transform($data);

        return $this;
    }

    public function delay(int $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * @param  array<non-empty-string, mixed>  $data
     * @return non-empty-string
     */
    private function transform(array $data): string
    {
        return implode(
            '|',
            array_map(
                static fn(string $key, mixed $value): string => \is_scalar($value)
                    ? "$key{->}$value"
                    : throw new InvalidArgumentException('Only scalar values allowed'),
                array_keys($data),
                $data,
            ),
        );
    }

    public function toArray(): array
    {
        return [
            ...$this->data,
            '_delay' => $this->delay,
        ];
    }
}
