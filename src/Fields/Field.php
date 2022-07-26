<?php
declare(strict_types=1);

namespace Leeto\MoonShine\Fields;

use Illuminate\Database\Eloquent\Model;
use Leeto\MoonShine\Contracts\RenderableContract;
use Leeto\MoonShine\Contracts\Fields\HasRelationshipContract;

use Leeto\MoonShine\Traits\Fields\FormElementTrait;
use Leeto\MoonShine\Traits\Fields\LinkTrait;
use Leeto\MoonShine\Traits\Fields\ShowWhenTrait;
use Leeto\MoonShine\Traits\Fields\WithFieldsTrait;
use Leeto\MoonShine\Traits\Fields\WithHtmlAttributes;
use Leeto\MoonShine\Traits\Fields\XModelTrait;
use Leeto\MoonShine\Helpers\ConditionHelpers;

abstract class Field implements RenderableContract
{
    use FormElementTrait, WithHtmlAttributes, ShowWhenTrait, XModelTrait, LinkTrait;

    public bool $showOnIndex = true;

    public bool $showOnExport = false;

    public bool $showOnForm = true;

    protected Field|null $parent = null;

    protected string $hint = '';

    protected bool $sortable = false;

    protected bool $removable = false;

    protected array $assets = [];

    public function showOnIndex($condition = null): static
    {
        $this->showOnIndex = ConditionHelpers::boolean($condition, true);

        return $this;
    }

    public function hideOnIndex($condition = null): static
    {
        $this->showOnIndex = ConditionHelpers::boolean($condition, false);

        return $this;
    }

    public function showOnForm($condition = null): static
    {
        $this->showOnForm = ConditionHelpers::boolean($condition, true);

        return $this;
    }

    public function hideOnForm($condition = null): static
    {
        $this->showOnForm = ConditionHelpers::boolean($condition, false);

        return $this;
    }

    public function showOnExport($condition = null): static
    {
        $this->showOnExport = ConditionHelpers::boolean($condition, true);

        return $this;
    }

    public function hideOnExport($condition = null): static
    {
        $this->showOnExport = ConditionHelpers::boolean($condition, false);

        return $this;
    }

    public function parent(): Field|null
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return $this->parent instanceof Field;
    }

    protected function setParent(Field $field): static
    {
        $this->parent = $field;

        return $this;
    }

    public function setParents(): static
    {
        if ($this->hasFields()) {
            $fields = [];

            foreach ($this->fields as $field) {
                $field = $field->setParents();

                $fields[] = $field->setParent($this);
            }

            $this->fields($fields);
        }

        return $this;
    }

    public function hasFields(): bool
    {
        return in_array(WithFieldsTrait::class, class_uses_recursive($this));
    }

    public function hint(string $hint): static
    {
        $this->hint = $hint;

        return $this;
    }

    public function getHint(): string
    {
        return $this->hint;
    }

    public function removable(): static
    {
        $this->removable = true;

        return $this;
    }

    public function isRemovable(): bool
    {
        return $this->removable;
    }

    public function sortable(): static
    {
        $this->sortable = true;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getAssets(): array
    {
        return $this->assets;
    }

    public function getView(): string
    {
        return 'moonshine::fields.' . static::$view;
    }

    public function formViewValue(Model $item): mixed
    {
        if ($this instanceof HasRelationshipContract
            && $this->isRelationToOne()
            && !$this->isRelationHasOne()) {
            return $item->{$this->relation()}?->getKey() ?? $this->getDefault();
        }

        if ($this instanceof HasRelationshipContract) {
            return $item->{$this->relation()} ?? $this->getDefault();
        }

        return $item->{$this->field()} ?? $this->getDefault();
    }

    public function indexViewValue(Model $item, bool $container = true): string
    {
        if ($this instanceof HasRelationshipContract) {
            if (!$item->{$this->relation()}) {
                return '';
            }

            return $container ? view('moonshine::shared.badge', [
                'color' => 'purple',
                'value' => $item->{$this->relation()}->{$this->resourceTitleField()}
            ]) : $item->{$this->relation()}->{$this->resourceTitleField()};
        }

        return $item->{$this->field()} ?? '';
    }

    public function exportViewValue(Model $item): string
    {
        if ($this instanceof HasRelationshipContract) {
            if (!$item->{$this->relation()}) {
                return '';
            }

            return $item->{$this->relation()}->{$this->resourceTitleField()} ?? '';
        }

        return $item->{$this->field()} ?? '';
    }

    public function save(Model $item): Model
    {
        $item->{$this->field()} = $this->requestValue() !== false
            ? $this->requestValue()
            : ($this->isNullable() ? null : '');

        return $item;
    }
}
