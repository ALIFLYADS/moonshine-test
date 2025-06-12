<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Resources;

use Closure;
use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\TableBuilderContract;
use MoonShine\Core\Resources\Resource;
use MoonShine\Core\TypeCasts\MixedDataCaster;
use MoonShine\Laravel\Concerns\Resource\HasFilters;
use MoonShine\Laravel\Concerns\Resource\HasHandlers;
use MoonShine\Laravel\Concerns\Resource\HasListComponent;
use MoonShine\Laravel\Concerns\Resource\HasQueryTags;
use MoonShine\Laravel\Contracts\HasFiltersContract;
use MoonShine\Laravel\Contracts\HasHandlersContract;
use MoonShine\Laravel\Contracts\HasQueryTagsContract;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Traits\Resource\ResourceActions;
use MoonShine\Laravel\Traits\Resource\ResourceCrudRouter;
use MoonShine\Laravel\Traits\Resource\ResourceEvents;
use MoonShine\Laravel\Traits\Resource\ResourceQuery;
use MoonShine\Laravel\Traits\Resource\ResourceWithAuthorization;
use MoonShine\Laravel\Traits\Resource\ResourceWithFields;
use MoonShine\Laravel\Traits\Resource\ResourceWithButtons;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Traversable;

/**
 * @template TData of mixed
 * @template-covariant TIndexPage of null|CrudPageContract = null
 * @template-covariant TFormPage of null|CrudPageContract = null
 * @template-covariant TDetailPage of null|CrudPageContract = null
 * @template TFields of FieldsContract = \MoonShine\Laravel\Collections\Fields
 * @template-covariant TItems of Traversable = \Illuminate\Support\Enumerable
 *
 * @implements CrudResourceContract<TData, TIndexPage, TFormPage, TDetailPage, TFields, TItems>
 * @extends Resource<CrudPageContract>
 */
abstract class CrudResource extends Resource implements
    CrudResourceContract,
    HasQueryTagsContract,
    HasHandlersContract,
    HasFiltersContract
{
    use HasFilters;
    use HasHandlers;
    use HasQueryTags;

    use HasListComponent;

    use ResourceWithButtons;
    use ResourceActions;
    use ResourceWithAuthorization;

    use ResourceWithFields;

    /** @use ResourceCrudRouter<TData> */
    use ResourceCrudRouter;

    /** @use ResourceEvents<TData> */
    use ResourceEvents;

    /** @use ResourceQuery<TData, TItems> */
    use ResourceQuery;

    protected string $column = 'id';

    protected bool $deleteRelationships = false;

    protected ?string $casterKeyName = null;

    protected bool $isRecentlyCreated = false;

    protected ?PageContract $activePage = null;

    protected bool $isAsync = false;

    protected bool $createInModal = false;

    protected bool $editInModal = false;

    protected bool $detailInModal = false;

    /**
     * @param  array<int, int>  $ids
     */
    abstract public function massDelete(array $ids): void;

    /**
     * @param  TData  $item
     */
    abstract public function delete(mixed $item, ?FieldsContract $fields = null): bool;

    /**
     * @param  TData  $item
     *
     * @return TData
     */
    abstract public function save(mixed $item, ?FieldsContract $fields = null): mixed;

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
            IndexPage::class,
            FormPage::class,
            DetailPage::class,
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
        return $this->getActivePage() instanceof IndexPage;
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
        return $this->getActivePage() instanceof FormPage;
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
        return $this->getActivePage() instanceof DetailPage;
    }

    public function getCaster(): DataCasterContract
    {
        return new MixedDataCaster($this->casterKeyName);
    }

    public function getCastedData(): ?DataWrapperContract
    {
        if (\is_null($this->getItem())) {
            return null;
        }

        return $this->getCaster()->cast($this->getItem());
    }

    /**
     * @return TData
     */
    public function getDataInstance(): mixed
    {
        return [];
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
     * @return null|Closure(iterable $items, TableBuilderContract $table): iterable
     */
    public function getItemsResolver(): ?Closure
    {
        return null;
    }

    /**
     * @param  TData  $item
     */
    public function modifyResponse(mixed $item): mixed
    {
        return $item;
    }

    /**
     * @param  iterable<TData>  $items
     */
    public function modifyCollectionResponse(mixed $items): mixed
    {
        return $items;
    }

    public function modifyDestroyResponse(MoonShineJsonResponse $response): MoonShineJsonResponse
    {
        return $response;
    }

    public function modifyMassDeleteResponse(MoonShineJsonResponse $response): MoonShineJsonResponse
    {
        return $response;
    }

    public function modifySaveResponse(MoonShineJsonResponse $response): MoonShineJsonResponse
    {
        return $response;
    }

    public function modifyErrorResponse(Response $response, Throwable $exception): Response
    {
        return $response;
    }
}
