<?php

declare(strict_types=1);

namespace MoonShine\Fields;

use Illuminate\Support\Collection;
use MoonShine\Contracts\Decorations\FieldsDecoration;
use MoonShine\Contracts\Fields\Fileable;
use MoonShine\Decorations\Decoration;
use MoonShine\Decorations\Tabs;
use MoonShine\Exceptions\FieldsException;
use MoonShine\Fields\Relationships\ModelRelationField;
use ReflectionClass;
use ReflectionException;
use Throwable;

abstract class FormElements extends Collection
{
    /**
     * @throws Throwable
     */
    public function withParents(): FormElements
    {
        return $this->onlyFields()->map(
            static fn (
                FormElement $formElement
            ): FormElement => $formElement->setParents()
        );
    }

    /**
     * @return FormElements<Field>
     * @throws Throwable
     */
    public function onlyFields(): FormElements
    {
        $fieldsOrDecorations = [];

        $this->extractFields($this->toArray(), $fieldsOrDecorations);

        return self::make($fieldsOrDecorations);
    }

    /**
     * @throws Throwable
     */
    private function extractFields($fieldsOrDecorations, array &$fields): void
    {
        foreach ($fieldsOrDecorations as $fieldOrDecoration) {
            if ($fieldOrDecoration instanceof FormElement) {
                $fields[] = $fieldOrDecoration;
            } elseif ($fieldOrDecoration instanceof Tabs) {
                foreach ($fieldOrDecoration->tabs() as $tab) {
                    $this->extractFields($tab->getFields(), $fields);
                }
            } elseif ($fieldOrDecoration instanceof Decoration) {
                $this->extractFields($fieldOrDecoration->getFields(), $fields);
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function prepareAttributes(): FormElements
    {
        return $this->onlyFields()->map(
            static function (FormElement $formElement): FormElement {
                $formElement->when(
                    ! $formElement instanceof Fileable,
                    function ($field): void {
                        $field->customAttributes(
                            ['x-on:change' => 'onChangeField($event)']
                        );
                    }
                );

                return $formElement;
            }
        );
    }

    /**
     * @throws Throwable
     */
    public function whenFieldsConditions(): FormElements
    {
        return $this->onlyFields()
            ->filter(
                static fn (FormElement $field): bool => $field->hasShowWhen()
            )
            ->map(
                static fn (
                    FormElement $field
                ): array => $field->showWhenCondition()
            );
    }

    /**
     * @throws Throwable
     */
    public function isWhenConditionField(string $name): bool
    {
        return $this->whenFieldNames()->has($name);
    }

    /**
     * @throws Throwable
     */
    public function whenFieldNames(): FormElements
    {
        return $this->whenFields()->mapWithKeys(
            static fn (FormElement $field): array => [
                $field->showWhenCondition()['changeField'] => $field->showWhenCondition()['changeField'],
            ]
        );
    }

    /**
     * @return FormElements<Field>
     * @throws Throwable
     */
    public function whenFields(): FormElements
    {
        return $this->onlyFields()
            ->filter(
                static fn (FormElement $field): bool => $field->hasShowWhen()
            )
            ->values();
    }

    /**
     * @return array<string, string>
     * @throws Throwable
     */
    public function extractLabels(): array
    {
        return $this->onlyFields()->flatMap(
            static fn (Field $field): array => [$field->column() => $field->label()]
        )->toArray();
    }


    /**
     * @param  ?ModelRelationField  $default
     * @throws Throwable
     */
    public function findByResourceClass(
        string $resource,
        ModelRelationField $default = null
    ): ?ModelRelationField {
        return $this->onlyRelationFields()->first(
            static fn (ModelRelationField $field): bool => get_class($field->getResource()) === $resource,
            $default
        );
    }

    /**
     * @param  ?ModelRelationField  $default
     * @throws Throwable
     */
    public function findByRelation(
        string $relation,
        ModelRelationField $default = null
    ): ?ModelRelationField {
        return $this->onlyRelationFields()->first(
            static fn (ModelRelationField $field): bool => $field->getRelationName() === $relation,
            $default
        );
    }

    /**
     * @param  ?FormElement  $default
     * @throws Throwable
     */
    public function findByColumn(
        string $column,
        FormElement $default = null
    ): ?FormElement {
        return $this->onlyFields()->first(
            static fn (FormElement $field): bool => $field->column() === $column,
            $default
        );
    }

    /**
     * @param  ?FormElement  $default
     * @throws Throwable
     */
    public function findByClass(
        string $class,
        FormElement $default = null
    ): ?FormElement {
        return $this->onlyFields()->first(
            static fn (FormElement $field): bool => $field::class === $class,
            $default
        );
    }

    /**
     * @throws Throwable
     */
    public function onlyColumns(): FormElements
    {
        return $this->onlyFields()->transform(
            static fn (FormElement $field): string => $field->column()
        );
    }

    /**
     * @throws ReflectionException|FieldsException
     */
    public function wrapIntoDecoration(
        string $class,
        string $label
    ): FormElements {
        $reflectionClass = new ReflectionClass($class);

        if (! $reflectionClass->implementsInterface(FieldsDecoration::class)) {
            throw FieldsException::wrapError();
        }

        return self::make([new $class($label, $this->toArray())]);
    }

    public function unwrapFields(string $class): FormElements
    {
        $modified = self::make();

        $this->each(
            static function ($fieldOrDecoration) use ($class, $modified): void {
                if ($fieldOrDecoration instanceof $class) {
                    $fieldOrDecoration
                        ->getFields()
                        ->each(
                            fn ($inner): Collection => $modified->push($inner)
                        );
                } else {
                    $modified->push($fieldOrDecoration);
                }
            }
        );

        return $modified;
    }
}
