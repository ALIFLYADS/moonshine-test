<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts;

interface HasFormValidationContract
{
    public function getRules(): array;

    /**
     * @return array<string, string[]|string>
     */
    public function validationMessages(): array;

    public function prepareForValidation(): void;

    public function hasErrorsAbove(): bool;

    public function isPrecognitive(): bool;
}
