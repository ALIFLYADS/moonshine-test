<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Resources;

use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\TableBuilderContract;
use MoonShine\Core\Resources\Resource;
use MoonShine\Core\TypeCasts\MixedDataCaster;
use MoonShine\Laravel\Concerns\Resource\HasCrudResponseModifiers;
use MoonShine\Laravel\Concerns\Resource\HasFilters;
use MoonShine\Laravel\Concerns\Resource\HasHandlers;
use MoonShine\Laravel\Concerns\Resource\HasListComponent;
use MoonShine\Laravel\Concerns\Resource\HasQueryTags;
use MoonShine\Laravel\Contracts\HasFiltersContract;
use MoonShine\Laravel\Contracts\HasHandlersContract;
use MoonShine\Laravel\Contracts\HasQueryTagsContract;
use MoonShine\Laravel\Contracts\Page\DetailPageContract;
use MoonShine\Laravel\Contracts\Page\FormPageContract;
use MoonShine\Laravel\Contracts\Page\IndexPageContract;
use MoonShine\Laravel\Contracts\Resource\HasCrudResponseModifiersContract;
use MoonShine\Laravel\Traits\Resource\ResourceActions;
use MoonShine\Laravel\Traits\Resource\ResourceCrudRouter;
use MoonShine\Laravel\Traits\Resource\ResourceEvents;
use MoonShine\Laravel\Traits\Resource\ResourceQuery;
use MoonShine\Laravel\Traits\Resource\ResourceWithAuthorization;
use MoonShine\Laravel\Traits\Resource\ResourceWithButtons;
use MoonShine\Laravel\Traits\Resource\ResourceWithFields;
use Traversable;

/**
 * @template TData of mixed
 * @template-covariant TIndexPage of null|CrudPageContract = null
 * @template-covariant TFormPage of null|CrudPageContract = null
 * @template-covariant TDetailPage of null|CrudPageContract = null
 * @template TFields of FieldsContract = \MoonShine\Laravel\Collections\Fields
 *
 * @implements CrudResourceContract<TData, TIndexPage, TFormPage, TDetailPage, TFields>
 * @extends Resource<CrudPageContract>
 */
abstract class CrudResource extends Resource implements
    CrudResourceContract,
    HasQueryTagsContract,
    HasHandlersContract,
    HasFiltersContract,
    HasCrudResponseModifiersContract
{
    use HasFilters;
    use HasHandlers;
    use HasQueryTags;
    use HasCrudResponseModifiers;

    use HasListComponent;

    use ResourceWithButtons;
    use ResourceActions;
    use ResourceWithAuthorization;

    use ResourceWithFields;

    /** @use ResourceCrudRouter<TData> */
    use ResourceCrudRouter;

    /** @use ResourceEvents<TData> */
    use ResourceEvents;

    /** @use ResourceQuery<TData> */
    use ResourceQuery;

    protected string $column = 'id';

    protected bool $deleteRelationships = false;

    protected ?string $casterKeyName = null;

    protected bool $isRecentlyCreated = false;

    protected ?PageContract $activePage = null;

    protected bool $isAsync = true;

    protected bool $createInModal = false;

    protected bool $editInModal = false;

    protected bool $detailInModal = false;

    /**
     * @return null|DataWrapperContract<TData>
     */
    abstract public function findItem(bool $orFail = false): ?DataWrapperContract;

    /**
     * @return iterable<TData>|Collection<array-key, TData>|LazyCollection<array-key, TData>|CursorPaginator<array-key, TData>|Paginator<array-key, TData>
     */
    abstract public function getItems(): iterable|Collection|LazyCollection|CursorPaginator|Paginator;

    /**
     * @param  array<int|string>  $ids
     */
    abstract public function massDelete(array $ids): void;

    /**
     * @param  DataWrapperContract<TData>  $item
     */
    abstract public function delete(DataWrapperContract $item, ?FieldsContract $fields = null): bool;

    /**
     * @param  DataWrapperContract<TData>  $item
     *
     * @return DataWrapperContract<TData>
     */
    abstract public function save(DataWrapperContract $item, ?FieldsContract $fields = null): DataWrapperContract;

    public function isRecentlyCreated(): bool
    {
        return $this->isRecentlyCreated;
    }

    public function flushState(): void
    {
        $this->item = null;
        $this->itemID = null;
        $this->pages = null;
        $this->activePage = null;
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            IndexPageContract::class,
            FormPageContract::class,
            DetailPageContract::class,
        ];
    }

    public function isAsync(): bool
    {
        return $this->isAsync;
    }

    public function isCreateInModal(): bool
    {
        return $this->createInModal;
    }

    public function isEditInModal(): bool
    {
        return $this->editInModal;
    }

    public function isDetailInModal(): bool
    {
        return $this->detailInModal;
    }

    /**
     * @return null|TIndexPage
     */
    public function getIndexPage(): ?PageContract
    {
        return $this->getPages()->indexPage();
    }

    public function isIndexPage(): bool
    {
        return $this->getActivePage() instanceof IndexPageContract;
    }

    /**
     * @return null|TFormPage
     */
    public function getFormPage(): ?PageContract
    {
        return $this->getPages()->formPage();
    }

    public function isFormPage(): bool
    {
        return $this->getActivePage() instanceof FormPageContract;
    }

    public function isCreateFormPage(): bool
    {
        return $this->isFormPage() && \is_null($this->getItemID());
    }

    public function isUpdateFormPage(): bool
    {
        return $this->isFormPage() && ! \is_null($this->getItemID());
    }

    public function setActivePage(?PageContract $page): void
    {
        $this->activePage = $page;
    }

    public function getActivePage(): ?PageContract
    {
        return $this->activePage ?? $this->getPages()->activePage();
    }

    /**
     * @return null|TDetailPage
     */
    public function getDetailPage(): ?PageContract
    {
        return $this->getPages()->detailPage();
    }

    public function isDetailPage(): bool
    {
        return $this->getActivePage() instanceof DetailPageContract;
    }

    public function getCaster(): DataCasterContract
    {
        return new MixedDataCaster($this->casterKeyName);
    }

    /**
     * @return DataWrapperContract<TData>|null
     */
    public function getCastedData(): ?DataWrapperContract
    {
        if (\is_null($this->getItem())) {
            return null;
        }

        return $this->getItem();
    }

    /**
     * @return DataWrapperContract<TData>
     */
    public function getDataInstance(): DataWrapperContract
    {
        return $this->getCaster()->cast([]);
    }

    public function getColumn(): string
    {
        return $this->column;
    }


    public function isDeleteRelationships(): bool
    {
        return $this->deleteRelationships;
    }

    /**
     * @return string[]
     */
    protected function search(): array
    {
        return ['id'];
    }

    public function hasSearch(): bool
    {
        return $this->search() !== [];
    }

    /**
     * @return string[]
     */
    public function getSearchColumns(): array
    {
        return $this->search();
    }

    /**
     * @return null|Closure(iterable<TData> $items, TableBuilderContract $table): iterable<TData>
     */
    public function getItemsResolver(): ?Closure
    {
        return null;
    }

    /**
     * @param  DataWrapperContract<TData>  $item
     */
    public function modifyResponse(DataWrapperContract $item): Jsonable
    {
        return $item->getOriginal();
    }

    /**
     * @param  iterable<TData>  $items
     */
    public function modifyCollectionResponse(mixed $items): Jsonable
    {
        return $items;
    }
}
