<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Traits\Resource;

use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Buttons\CreateButton;
use MoonShine\Laravel\Buttons\DeleteButton;
use MoonShine\Laravel\Buttons\DetailButton;
use MoonShine\Laravel\Buttons\EditButton;
use MoonShine\Laravel\Buttons\FiltersButton;
use MoonShine\Laravel\Buttons\MassDeleteButton;
use MoonShine\Laravel\Resources\CrudResource;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

trait ResourceWithButtons
{
    /**
     * @throws Throwable
     */
    public function getCreateButton(
        ?CrudResource $resource = null,
        ?string $componentName = null,
        bool $isAsync = true,
        string $modalName = 'resource-create-modal'
    ): ActionButtonContract {
        return CreateButton::for(
            $resource ?? $this,
            componentName: $componentName,
            isAsync: $isAsync,
            modalName: $modalName
        );
    }

    /**
     * @throws Throwable
     */
    public function getEditButton(
        ?CrudResource $resource = null,
        ?string $componentName = null,
        bool $isAsync = true,
        string $modalName = 'resource-edit-modal'
    ): ActionButtonContract {
        return EditButton::for(
            $resource ?? $this,
            componentName: $componentName,
            isAsync: $isAsync,
            modalName: $modalName,
        );
    }

    public function getDetailButton(
        ?CrudResource $resource = null,
        string $modalName = 'resource-detail-modal',
        bool $isSeparateModal = true
    ): ActionButtonContract {
        return DetailButton::for(
            $resource ?? $this,
            $modalName,
            $isSeparateModal
        );
    }

    public function getDeleteButton(
        ?CrudResource $resource = null,
        ?string $componentName = null,
        ?string $redirectAfterDelete = null,
        bool $isAsync = true,
        string $modalName = 'resource-delete-modal',
    ): ActionButtonContract {
        return DeleteButton::for(
            $resource ?? $this,
            componentName: $componentName,
            redirectAfterDelete: $isAsync ? null : $redirectAfterDelete,
            isAsync: $isAsync,
            modalName: $modalName
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getFiltersButton(?CrudResource $resource = null,): ActionButtonContract
    {
        return FiltersButton::for($resource ?? $this);
    }

    public function getMassDeleteButton(
        ?CrudResource $resource = null,
        ?string $componentName = null,
        ?string $redirectAfterDelete = null,
        bool $isAsync = true,
        string $modalName = 'resource-mass-delete-modal',
    ): ActionButtonContract {
        return MassDeleteButton::for(
            $resource ?? $this,
            componentName: $componentName,
            redirectAfterDelete: $isAsync ? null : $redirectAfterDelete,
            isAsync: $isAsync,
            modalName: $modalName
        );
    }
}
