<?php

declare(strict_types=1);

namespace MoonShine\Core\TypeCasts;

use ArrayAccess;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;

final readonly class MixedDataWrapper implements DataWrapperContract, ArrayAccess
{
    public function __construct(private mixed $data, private string|int|null $key = null)
    {
    }

    public function getOriginal(): mixed
    {
        return $this->data;
    }

    public function getKey(): int|string|null
    {
        return $this->key;
    }

    public function toArray(): array
    {
        if (\is_object($this->data) && method_exists($this->data, 'toArray')) {
            return $this->data->toArray();
        }

        return (array) $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        if(\is_array($this->data)) {
            return isset($this->data[$offset]);
        }

        return \property_exists($this->data, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if(\is_array($this->data)) {
            return $this->data[$offset];
        }

        return $this->data->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {}

    public function offsetUnset(mixed $offset): void
    {}
}
