<?php

declare(strict_types=1);

namespace MoonShine\Fields;

use Closure;
use MoonShine\Contracts\Fields\HasFields;
use MoonShine\Traits\WithFields;

class StackFields extends Field implements HasFields
{
    use WithFields;

    protected string $view = 'moonshine::fields.stack';
    protected bool $withWrapper = false;

    protected bool $withLabels = false;

    public function withLabels(): static
    {
        $this->withLabels = true;

        return $this;
    }

    public function hasLabels(): bool
    {
        return $this->withLabels;
    }

    protected function resolveOnSave(): ?Closure
    {
        return function ($item) {
            $this->getFields()->onlyFields()->each(
                static function (Field $field) use (&$item): void {
                    $item = $field->save(
                        fn($item) => $item,
                        $item
                    );
                }
            );

            return $item;
        };
    }

    protected function resolvePreview(): string
    {
        return view($this->getView(), [
            'element' => $this,
            'indexView' => true,
        ])->render();
    }

    protected function resolveAfterSave(mixed $item): void
    {
        parent::resolveAfterSave($item);

        $this->getFields()
            ->onlyFields()
            ->each(fn (FormElement $field) => $field->afterSave($item));
    }
}
