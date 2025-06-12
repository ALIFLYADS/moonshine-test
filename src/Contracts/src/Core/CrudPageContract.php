<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\Collection\ActionButtonsContract;

/**
 * @template TFields of FieldsContract
 */
interface CrudPageContract extends PageContract
{
    /**
     * @return TFields
     */
    public function getFields(): FieldsContract;

    public function isAsync(): bool;

    public function getEmptyModals(): array;

    public function getButtons(): ActionButtonsContract;
}
