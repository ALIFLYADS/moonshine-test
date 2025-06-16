<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use Illuminate\Contracts\Support\Jsonable;

/**
 * @internal
 * @template TData
 *
 */
interface CrudResourceWithResponseModifiersContract
{
    /**
     * @param DataWrapperContract<TData> $item
     */
    public function modifyResponse(DataWrapperContract $item): Jsonable;

    /**
     * @param  iterable<TData>  $items
     */
    public function modifyCollectionResponse(iterable $items): Jsonable;
}
