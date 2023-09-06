<?php

namespace MoonShine\Traits\Fields;

use Closure;
use MoonShine\Fields\Field;
use MoonShine\Support\Condition;

trait Applies
{
    protected bool $canApply = true;

    protected ?Closure $onApply = null;

    protected ?Closure $onBeforeApply = null;

    protected ?Closure $onAfterApply = null;

    protected ?Closure $onAfterDestroy = null;

    public function canApply(mixed $condition = null): static
    {
        $this->canApply = Condition::boolean($condition, true);

        return $this;
    }

    public function isCanApply(): bool
    {
        return $this->canApply;
    }

    protected function resolveOnApply(): ?Closure
    {
        return $this->onApply;
    }

    protected function resolveBeforeApply(mixed $data): void
    {
        // Logic here
    }

    protected function resolveAfterApply(mixed $data): void
    {
        // Logic here
    }

    protected function resolveAfterDestroy(mixed $data): void
    {
        // Logic here
    }

    public function onApply(Closure $onApply): static
    {
        $this->onApply = $onApply;

        return $this;
    }

    public function apply(Closure $default, mixed $data): mixed
    {
        if (! $this->isCanApply()) {
            return $data;
        }

        if (! is_closure($this->onApply)) {
            $classApply = findFieldApply(
                $this,
                'fields',
                'all',
            );

            $this->when(
                ! is_null($classApply),
                fn (Field $field): Field => $field->onApply($classApply->apply($field))
            );
        }


        $applyFunction = is_closure($this->onApply)
            ? $this->onApply
            : $this->resolveOnApply();

        return is_closure($applyFunction)
            ? $applyFunction($data)
            : $default($data);
    }

    public function beforeApply(mixed $data): void
    {
        is_closure($this->onBeforeApply)
            ? call_user_func($this->onBeforeApply, $data)
            : $this->resolveBeforeApply($data);
    }

    public function afterApply(mixed $data): void
    {
        is_closure($this->onAfterApply)
            ? call_user_func($this->onAfterApply, $data)
            : $this->resolveAfterApply($data);
    }

    public function afterDestroy(mixed $data): void
    {
        is_closure($this->onAfterDestroy)
            ? call_user_func($this->onAfterDestroy, $data)
            : $this->resolveAfterDestroy($data);
    }

    public function onBeforeApply(Closure $onBeforeApply): static
    {
        $this->onBeforeApply = $onBeforeApply;

        return $this;
    }

    public function onAfterApply(Closure $onAfterApply): static
    {
        $this->onAfterApply = $onAfterApply;

        return $this;
    }

    public function onAfterDestroy(Closure $onAfterDestroy): static
    {
        $this->onAfterDestroy = $onAfterDestroy;

        return $this;
    }
}
