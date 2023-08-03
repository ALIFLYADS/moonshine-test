<?php

declare(strict_types=1);

namespace MoonShine\Fields;

use Closure;
use MoonShine\Contracts\Fields\HasDefaultValue;
use MoonShine\Helpers\Condition;
use MoonShine\Traits\Fields\FieldActionTrait;
use MoonShine\Traits\Fields\LinkTrait;
use MoonShine\Traits\Fields\ShowOrHide;
use MoonShine\Traits\WithIsNowOnRoute;

abstract class Field extends FormElement
{
    use ShowOrHide;
    use LinkTrait;
    use WithIsNowOnRoute;
    use FieldActionTrait;

    protected mixed $rawValue = null;

    protected bool $rawMode = false;

    protected mixed $value = null;

    protected ?Closure $previewCallback = null;

    protected bool $sortable = false;

    protected bool $required = false;

    protected bool $disabled = false;

    protected bool $readonly = false;

    protected bool $hidden = false;

    protected bool $canSave = true;

    /**
     * Define whether if index page can be sorted by this field
     *
     * @return $this
     */
    public function sortable(): static
    {
        $this->sortable = true;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function sortQuery(): string
    {
        return request()->fullUrlWithQuery([
            'order' => [
                'field' => $this->column(),
                'type' => $this->sortActive() && $this->sortType('asc') ? 'desc'
                    : 'asc',
            ],
        ]);
    }

    public function sortActive(): bool
    {
        return request()->has('order.field')
            && request('order.field') === $this->column();
    }

    public function sortType(string $type): bool
    {
        return request()->has('order.type')
            && request('order.type') === strtolower($type);
    }

    public function setValue(mixed $value = null): self
    {
        $this->value = $value;

        return $this;
    }

    protected function setRawValue(mixed $value = null): void
    {
        $this->rawValue = $value;
    }

    public function fillValues(array $rawValues = [], mixed $castedValues = null): self
    {
        if($this->value) {
            return $this;
        }

        $value = $rawValues[$this->column()] ?? null;

        $this->setRawValue($value);

        if (is_callable($this->valueCallback())) {
            $value = call_user_func(
                $this->valueCallback(),
                empty($castedValues) ? $rawValues : $castedValues
            );
        }

        $this->setValue($value);

        return $this;
    }

    public function rawMode(Closure|bool|null $condition = null): static
    {
        $this->rawMode = Condition::boolean($condition, true);

        return $this;
    }

    public function isRawMode(): bool
    {
        return $this->rawMode;
    }

    public function toRawValue(): mixed
    {
        return $this->rawValue;
    }

    public function toValue(bool $withDefault = true): mixed
    {
        $default = $withDefault && $this instanceof HasDefaultValue
            ? $this->getDefault()
            : null;

        return $this->value ?? $default;
    }

    public function value(): mixed
    {
        $old = old($this->nameDot());

        if ($old) {
            return $old;
        }

        return $this->resolveValue();
    }

    protected function resolveValue(): mixed
    {
        return $this->toValue();
    }

    public function changePreview(Closure $closure): static
    {
        $this->previewCallback = $closure;

        return $this;
    }

    public function isPreviewChanged(): bool
    {
        return ! is_null($this->previewCallback);
    }

    public function preview(): string
    {
        if($this->isPreviewChanged()) {
            return (string) call_user_func(
                $this->previewCallback,
                $this->toValue(),
                $this->resolvePreview(),
            );
        }

        return $this->resolvePreview();
    }

    protected function resolvePreview(): string
    {
        return (string) ($this->toValue() ?? '');
    }

    public function canSave(mixed $condition = null): static
    {
        $this->canSave = Condition::boolean($condition, true);

        return $this;
    }

    public function isCanSave(): bool
    {
        return $this->canSave;
    }

    public function type(): string
    {
        return $this->hidden
            ? 'hidden'
            : $this->attributes()->get('type', '');
    }

    public function isFile(): bool
    {
        return $this->type() === 'file';
    }

    public function required(Closure|bool|null $condition = null): static
    {
        $this->required = Condition::boolean($condition, true);
        $this->setAttribute('required', $this->required);

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function disabled(Closure|bool|null $condition = null): static
    {
        $this->disabled = Condition::boolean($condition, true);
        $this->setAttribute('disabled', $this->disabled);

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function hidden(Closure|bool|null $condition = null): static
    {
        $this->hidden = Condition::boolean($condition, true);

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden
            || $this->attributes()->get('type') === 'hidden';
    }

    public function readonly(Closure|bool|null $condition = null): static
    {
        $this->readonly = Condition::boolean($condition, true);
        $this->setAttribute('readonly', $this->readonly);

        return $this;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }
}
