<?php

declare(strict_types=1);

namespace MoonShine\UI\Fields;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\HasFieldsContract;
use MoonShine\UI\Collections\Fields;
use MoonShine\UI\Components\FieldsGroup;
use MoonShine\UI\Contracts\FieldsWrapperContract;
use MoonShine\UI\Traits\WithFields;
use Throwable;

/**
 * @implements  HasFieldsContract<Fields|FieldsContract>
 * @method static static make(string|Closure|null $label, iterable|Closure|FieldsContract $fields = [])
 */
class Fieldset extends Field implements HasFieldsContract, FieldsWrapperContract
{
    use WithFields;

    protected string $view = 'moonshine::fields.fieldset';

    protected bool $withWrapper = false;

    public function __construct(string|Closure|null $label, iterable|Closure|FieldsContract $fields = [])
    {
        parent::__construct($label);

        $this->fields($fields);
    }

    protected function resolveFill(array $raw = [], ?DataWrapperContract $casted = null, int $index = 0): static
    {
        $this->getFields()->fill($raw, $casted, $index);

        return parent::resolveFill($raw, $casted, $index);
    }

    protected function prepareFields(): FieldsContract
    {
        return $this
            ->getFields()
            ->fillClonedRecursively($this->getData()?->toArray() ?? [], $this->getData(), $this->getRowIndex());
    }

    /**
     * @throws Throwable
     */
    protected function resolvePreview(): Renderable|string
    {
        return FieldsGroup::make($this->getPreparedFields())
            ->previewMode()
            ->render();
    }

    protected function resolveOnApply(): ?Closure
    {
        return function ($item) {
            $this->getPreparedFields()->onlyFields()->each(
                static function (FieldContract $field) use ($item): void {
                    $field->apply(
                        static function (mixed $item) use ($field): mixed {
                            if ($field->getRequestValue() !== false) {
                                data_set($item, $field->getColumn(), $field->getRequestValue());
                            }

                            return $item;
                        },
                        $item
                    );
                }
            );

            return $item;
        };
    }

    /**
     * @throws Throwable
     */
    protected function resolveBeforeApply(mixed $data): mixed
    {
        $this->getPreparedFields()
            ->onlyFields()
            ->each(static fn (FieldContract $field): mixed => $field->beforeApply($data));

        return $data;
    }

    /**
     * @throws Throwable
     */
    protected function resolveAfterApply(mixed $data): mixed
    {
        $this->getPreparedFields()
            ->onlyFields()
            ->each(static fn (FieldContract $field): mixed => $field->afterApply($data));

        return $data;
    }

    /**
     * @throws Throwable
     */
    protected function resolveAfterDestroy(mixed $data): mixed
    {
        $this->getPreparedFields()
            ->onlyFields()
            ->each(
                static fn (FieldContract $field): mixed => $field
                ->fillData($data)
                ->afterDestroy($data)
            );

        return $data;
    }

    /**
     * @throws Throwable
     */
    protected function viewData(): array
    {
        return [
            'fields' => $this->getPreparedFields(),
        ];
    }
}
