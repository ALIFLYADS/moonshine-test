<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Page;

use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;

/**
 * @template TFields of FieldsContract
 *
 * @extends CrudPageContract<TFields>
 */
interface DetailPageContract extends CrudPageContract
{
}
