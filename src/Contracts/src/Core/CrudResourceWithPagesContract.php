<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;

/**
 * @internal
 * @template TData
 * @template-covariant TIndexPage of null|CrudPageContract = null
 * @template-covariant TFormPage of null|CrudPageContract = null
 * @template-covariant TDetailPage of null|CrudPageContract = null
 *
 */
interface CrudResourceWithPagesContract
{
    public function setActivePage(?PageContract $page): void;

    /**
     * @return null|TIndexPage
     */
    public function getIndexPage(): ?PageContract;

    /**
     * @return null|TFormPage
     */
    public function getFormPage(): ?PageContract;

    /**
     * @return null|TDetailPage
     */
    public function getDetailPage(): ?PageContract;

    public function getActivePage(): ?PageContract;

    public function getIndexPageUrl(array $params = [], null|string|array $fragment = null): string;

    /**
     * @param DataWrapperContract<TData>|int|string|null $key
     */
    public function getFormPageUrl(
        DataWrapperContract|int|string|null $key = null,
        array $params = [],
        null|string|array $fragment = null
    ): string;

    /**
     * @param DataWrapperContract<TData>|int|string $key
     */
    public function getDetailPageUrl(
        DataWrapperContract|int|string $key,
        array $params = [],
        null|string|array $fragment = null
    ): string;

    public function isIndexPage(): bool;

    public function isFormPage(): bool;

    public function isDetailPage(): bool;

    public function isCreateFormPage(): bool;

    public function isUpdateFormPage(): bool;
}
