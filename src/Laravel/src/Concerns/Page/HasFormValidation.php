<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use Illuminate\Contracts\Validation\Rule;
use Stringable;

/**
 * @template T
 */
trait HasFormValidation
{
    protected bool $errorsAbove = true;

    protected bool $isPrecognitive = false;

    /**
     * Get an array of validation rules for resource related model
     *
     * @param T $item
     *
     * @return array<string, string[]|string|list<Rule>|list<Stringable>>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    public function getRules(): array
    {
        return $this->rules(
            $this->getResource()->getItemOrInstance()->getOriginal()
        );
    }

    /**
     * Get custom messages for validator errors
     *
     * @return array<string, string[]|string>
     */
    public function validationMessages(): array
    {
        return [];
    }

    public function prepareForValidation(): void
    {
        // Logic
    }

    public function hasErrorsAbove(): bool
    {
        return $this->errorsAbove;
    }

    public function isPrecognitive(): bool
    {
        return $this->isPrecognitive;
    }
}
