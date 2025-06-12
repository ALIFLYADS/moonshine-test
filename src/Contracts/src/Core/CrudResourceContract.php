<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use Traversable;

/**
 * @template TData
 * @template-covariant TIndexPage of null|CrudPageContract
 * @template-covariant TFormPage of null|CrudPageContract
 * @template-covariant TDetailPage of null|CrudPageContract
 * @template TFields of FieldsContract
 * @template-covariant TItems of Traversable
 *
 * @extends CrudResourceWithPagesContract<TData, TIndexPage, TFormPage, TDetailPage>
 * @extends CrudResourceWithFieldsContract<TFields>
 * @extends CrudResourceWithResponseModifiersContract<TData>
 * @extends ResourceContract<CrudPageContract>
 */
interface CrudResourceContract extends
    ResourceContract,
    CrudResourceWithModalsContract,
    CrudResourceWithPagesContract,
    CrudResourceWithFieldsContract,
    CrudResourceWithResponseModifiersContract,
    CrudResourceWithQueryParamsContract,
    CrudResourceWithSearchContract,
    HasListComponentContract
{
    public function getColumn(): string;

    public function isAsync(): bool;

    /**
     * @param  DataWrapperContract<TData>|int|string|null  $key
     */
    public function getRoute(
        ?string $name = null,
        DataWrapperContract|int|string|null $key = null,
        array $query = []
    ): string;

    /**
     * @return DataCasterContract<TData>
     */
    public function getCaster(): DataCasterContract;

    /**
     * @return ?DataWrapperContract<TData>
     */
    public function getCastedData(): ?DataWrapperContract;

    /**
     * @return ?TData
     */
    public function getDataInstance(): mixed;

    /**
     * @param  ?TData  $item
     */
    public function setItem(mixed $item): static;

    /**
     * @return ?TData
     */
    public function getItem(): mixed;

    public function setItemID(int|string|false|null $itemID): static;

    public function getItemID(): int|string|null;

    /**
     * @return TData
     */
    public function getItemOrInstance(): mixed;

    public function isItemExists(): bool;

    /**
     * @return TItems
     */
    public function getItems(): mixed;

    /**
     * @return ?TData
     */
    public function findItem(bool $orFail = false): mixed;

    /**
     * @param  array<int|string>  $ids
     */
    public function massDelete(array $ids): void;

    /**
     * @param  TData  $item
     * @param ?TFields $fields
     *
     */
    public function delete(mixed $item, ?FieldsContract $fields = null): bool;

    /**
     * @param  TData  $item
     * @param ?TFields $fields
     *
     * @return TData
     */
    public function save(mixed $item, ?FieldsContract $fields = null): mixed;
}
