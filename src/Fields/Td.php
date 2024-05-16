<?php

declare(strict_types=1);

namespace MoonShine\Fields;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\ComponentAttributeBag;
use MoonShine\Components\FieldsGroup;
use MoonShine\Decorations\LineBreak;

/**
 * @method static static make(Closure|string $label, ?Closure $fields = null)
 */
class Td extends Template
{
    private ?Closure $conditionalFields = null;

    private ?Closure $tdAttributes = null;

    protected bool $withWrapper = false;

    protected bool $withLabels = false;

    public function __construct(Closure|string $label, ?Closure $fields = null)
    {
        parent::__construct($label);

        $this->conditionalFields($fields);
    }

    public function withLabels(): static
    {
        $this->withLabels = true;

        return $this;
    }

    public function hasLabels(): bool
    {
        return $this->withLabels;
    }

    /**
     * @param  ?Closure(mixed $data, $td self): self $fields
     * @return self
     */
    public function conditionalFields(?Closure $fields = null): self
    {
        $this->conditionalFields = $fields;

        return $this;
    }

    public function hasConditionalFields(): bool
    {
        return ! is_null($this->conditionalFields);
    }

    public function getConditionalFields(): array
    {
        return value($this->conditionalFields, $this->getData(), $this);
    }

    public function resolveFill(
        array $raw = [],
        mixed $casted = null,
        int $index = 0
    ): static {
        return $this
            ->setRawValue($raw)
            ->setData($casted ?? $raw)
            ->setRowIndex($index);
    }

    /**
     * @param  Closure(mixed $data, int $row, ComponentAttributeBag $attributes, $td self): ComponentAttributeBag  $attributes
     * @return self
     */
    public function tdAttributes(Closure $attributes): self
    {
        $this->tdAttributes = $attributes;

        return $this;
    }

    public function hasTdAttributes(): bool
    {
        return ! is_null($this->tdAttributes);
    }

    public function resolveTdAttributes(mixed $data, int $row, ComponentAttributeBag $attributes): ComponentAttributeBag
    {
        return $this->hasTdAttributes()
            ? value($this->tdAttributes, $data, $row, $attributes, $this)
            : $attributes;
    }

    protected function resolvePreview(): string|View
    {
        $fields = $this->hasConditionalFields()
            ? $this->getConditionalFields()
            : $this->getFields();

        return FieldsGroup::make(
            Fields::make($fields)
        )
            ->mapFields(function (Field $field) {
                return $field
                    ->resolveFill($this->toRawValue(), $this->getData())
                    ->beforeRender(fn() => $this->hasLabels() ? '' : (string) LineBreak::make())
                    ->withoutWrapper($this->hasLabels())
                    ->forcePreview();
            })
            ->render();
    }
}
