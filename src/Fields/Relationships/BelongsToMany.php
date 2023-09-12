<?php

declare(strict_types=1);

namespace MoonShine\Fields\Relationships;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\ComponentAttributeBag;
use MoonShine\Components\TableBuilder;
use MoonShine\Contracts\Fields\HasFields;
use MoonShine\Contracts\Fields\HasPivot;
use MoonShine\Contracts\Fields\Relationships\HasAsyncSearch;
use MoonShine\Contracts\Fields\Relationships\HasRelatedValues;
use MoonShine\Fields\Checkbox;
use MoonShine\Fields\Field;
use MoonShine\Fields\Fields;
use MoonShine\Fields\ID;
use MoonShine\Fields\NoInput;
use MoonShine\Fields\Text;
use MoonShine\Support\Condition;
use MoonShine\Traits\Fields\WithAsyncSearch;
use MoonShine\Traits\Fields\WithRelatedValues;
use MoonShine\Traits\WithFields;
use Throwable;

class BelongsToMany extends ModelRelationField implements
    HasRelatedValues,
    HasPivot,
    HasFields,
    HasAsyncSearch
{
    use WithFields;
    use WithRelatedValues;
    use WithAsyncSearch;

    protected string $view = 'moonshine::fields.relationships.belongs-to-many';

    protected bool $isGroup = true;

    protected bool $tree = false;

    protected string $treeHtml = '';

    protected string $treeParentColumn = '';

    protected bool $onlyCount = false;

    protected bool $inLine = false;

    protected string $inLineSeparator = '';

    protected bool $inLineBadge = false;

    protected bool $selectMode = false;

    protected bool $isCreatable = false;

    public function getView(): string
    {
        if ($this->isTree()) {
            return 'moonshine::fields.shared.tree';
        }

        return parent::getView();
    }

    public function onlyCount(): static
    {
        $this->onlyCount = true;

        return $this;
    }

    public function inLine(string $separator = '', bool $badge = false): static
    {
        $this->inLine = true;
        $this->inLineSeparator = $separator;
        $this->inLineBadge = $badge;

        return $this;
    }

    public function selectMode(): self
    {
        $this->selectMode = true;

        return $this;
    }

    public function creatable(Closure|bool|null $condition = null): static
    {
        $this->isCreatable = Condition::boolean($condition, true);

        return $this;
    }

    public function isCreatable(): bool
    {
        return $this->isCreatable;
    }

    public function isSelectMode(): bool
    {
        return $this->selectMode;
    }

    public function tree(string $parentColumn): static
    {
        $this->treeParentColumn = $parentColumn;
        $this->tree = true;

        return $this;
    }

    protected function isTree(): bool
    {
        return $this->tree;
    }

    public function toTreeHtml(): string
    {
        $data = $this->resolveValuesQuery()
            ->get()
            ->map(fn ($item) => $item->setAttribute($this->treeParentColumn, $item->{$this->treeParentColumn} ?? 0))
            ->groupBy($this->treeParentColumn)
            ->map(fn ($items): Collection => $items->keyBy($items->first()->getKeyName()));

        $this->treeHtml = '';

        return $this->buildTree($data);
    }

    protected function buildTree(Collection $data, int|string $parentKey = 0, int $offset = 0): string
    {
        if ($data->has($parentKey)) {
            foreach ($data->get($parentKey) as $item) {
                $element = view(
                    'moonshine::components.form.input-composition',
                    [
                        'attributes' => $this->attributes()->merge([
                            'type' => 'checkbox',
                            'id' => $this->id((string) $item->getKey()),
                            'name' => $this->name((string) $item->getKey()),
                            'value' => $item->getKey(),
                            'class' => 'form-group-inline',
                        ]),
                        'beforeLabel' => true,
                        'label' => $item->{$this->getResourceColumn()},
                    ]
                );

                $this->treeHtml .= str($element)->wrap(
                    "<li style='margin-left: " . ($offset * 30) . "px'>",
                    "</li>"
                );

                $this->buildTree($data, $item->getKey(), $offset + 1);
            }
        }

        return str($this->treeHtml)->wrap(
            "<ul class='tree-list'>",
            "</ul>"
        )->value();
    }

    protected function getPivotAs(): string
    {
        return $this->getRelation()?->getPivotAccessor() ?? 'pivot';
    }

    protected function getPivotName(): string
    {
        return "{$this->getRelationName()}_pivot";
    }

    public function selectedKeys(): Collection
    {
        return collect($this->toValue())->pluck($this->getRelation()?->getRelated()?->getKeyName() ?? 'id');
    }

    protected function preparedFields(): Fields
    {
        return $this->getFields()->onlyFields()->map(
            fn (Field $field): Field => (clone $field)
                ->setColumn("{$this->getPivotAs()}.{$field->column()}")
                ->setAttribute('class', 'pivotField')
                ->setName(
                    "{$this->getPivotName()}[\${index0}][{$field->column()}]"
                )
                ->setParent($this)
                ->iterableAttributes()
        );
    }

    protected function resolveValue(): mixed
    {
        $titleColumn = $this->getResourceColumn();
        $checkedColumn = "{$this->getRelationName()}[\${index0}]";
        $identityField = Checkbox::make('#', $checkedColumn)
            ->setAttribute('class', 'pivotChecker')
            ->setName($checkedColumn)
            ->iterableAttributes();

        $fields = $this->preparedFields()
            ->onlyFields()
            ->prepend(NoInput::make($titleColumn))
            ->prepend($identityField);

        $values = $this->resolveValuesQuery()->get();

        $values = $values->map(function ($value) use ($checkedColumn) {
            $checked = $this->toValue()
                ->first(fn ($item): bool => $item->getKey() === $value->getKey());

            return $value
                ->setRelations($checked?->getRelations() ?? $value->getRelations())
                ->setAttribute($checkedColumn, ! is_null($checked));
        });

        return TableBuilder::make(items: $values)
            ->fields($fields)
            ->cast($this->getModelCast())
            ->trAttributes(
                fn (
                    Model $data,
                    int $row,
                    ComponentAttributeBag $attributes
                ): ComponentAttributeBag => $attributes->merge([
                    'data-key' => $data->getKey(),
                ])
            )
            ->preview()
            ->simple()
            ->editable()
            ->reindex()
            ->withNotFound();
    }

    /**
     * @throws Throwable
     */
    protected function resolvePreview(): View|string
    {
        $values = $this->toValue() ?? [];
        $column = $this->getResourceColumn();

        if ($this->isRawMode()) {
            return $values
                ->map(fn (Model $item) => $item->{$column})
                ->implode(';');
        }

        if ($this->onlyCount) {
            return (string) $values->count();
        }

        if ($this->inLine) {
            return $values->implode(function (Model $item) use ($column) {
                $value = $item->{$column} ?? false;

                if (is_closure($this->formattedValueCallback())) {
                    $value = call_user_func(
                        $this->formattedValueCallback(),
                        $item
                    );
                }

                if ($this->inLineBadge) {
                    return view('moonshine::ui.badge', [
                        'color' => 'primary',
                        'value' => $value,
                        'margin' => true,
                    ])->render();
                }

                return $value;
            }, $this->inLineSeparator) ?? '';
        }

        $fields = $this->preparedFields()
            ->onlyFields()
            ->prepend(Text::make('#', $column))
            ->prepend(ID::make());

        return TableBuilder::make($fields, $values)
            ->preview()
            ->simple()
            ->cast($this->getModelCast())
            ->render();
    }

    protected function resolveOnApply(): ?Closure
    {
        return static fn ($item) => $item;
    }

    protected function resolveAfterApply(mixed $data): void
    {
        /* @var Model $item */
        $item = $data;
        $requestValues = array_filter($this->requestValue() ?: []);
        $applyValues = [];

        if($this->isSelectMode()) {
            $item->{$this->getRelationName()}()->sync($requestValues);

            return;
        }

        foreach ($requestValues as $key => $checked) {
            foreach ($this->getFields() as $field) {
                $field->setRequestKeyPrefix(
                    str("{$this->getPivotName()}.$key")->when(
                        $this->requestKeyPrefix(),
                        fn ($str) => $str->prepend("{$this->requestKeyPrefix()}.")
                    )->value()
                );

                $values = request($field->requestKeyPrefix());

                $apply = $field->apply(
                    fn ($data): mixed => data_set($data, $field->column(), $values[$field->column()]),
                    $values
                );

                data_set(
                    $applyValues[$key],
                    $field->column(),
                    data_get($apply, $field->column())
                );
            }
        }

        $item->{$this->getRelationName()}()->sync($applyValues);
    }
}
