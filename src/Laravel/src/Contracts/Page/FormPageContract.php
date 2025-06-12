<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Page;

use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\HasListComponentContract;
use MoonShine\Contracts\UI\Collection\ActionButtonsContract;
use MoonShine\Laravel\Contracts\HasFiltersContract;
use MoonShine\Laravel\Contracts\HasFormValidationContract;
use MoonShine\Laravel\Contracts\HasHandlersContract;
use MoonShine\Laravel\Contracts\HasMetricsContract;
use MoonShine\Laravel\Contracts\HasQueryTagsContract;

/**
 * @template TFields of FieldsContract
 *
 * @extends CrudPageContract<TFields>
 */
interface FormPageContract extends
    CrudPageContract,
    HasFormValidationContract
{
    public function getFormButtons(): ActionButtonsContract;
}
