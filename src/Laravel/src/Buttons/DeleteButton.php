<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Buttons;

use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Resources\CrudResource;
use MoonShine\Support\Enums\Ability;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\UI\Components\ActionButton;

final class DeleteButton
{
    /**
     * @param  CrudResource  $resource
     */
    public static function for(
        CrudResourceContract $resource,
        ?string $componentName = null,
        ?string $redirectAfterDelete = null,
        bool $isAsync = true,
        string $modalName = 'resource-delete-modal',
    ): ActionButtonContract {
        $action = static fn (mixed $item, ?DataWrapperContract $data): string => $resource->getRoute(
            'crud.destroy',
            $data?->getKey(),
            $redirectAfterDelete
            ? ['_redirect' => $redirectAfterDelete]
            : []
        );

        return ActionButton::make(
            '',
            url: $action
        )
            ->name('resource-delete-button')
            ->withoutLoading()
            ->withConfirm(
                method: HttpMethod::DELETE,
                formBuilder: static fn (FormBuilderContract $formBuilder): FormBuilderContract => $formBuilder->when(
                    $isAsync || $resource->isAsync(),
                    static fn (FormBuilderContract $form): FormBuilderContract => $form->async(
                        events: $resource->getListEventName(
                            $componentName ?? $resource->getListComponentName(),
                            $isAsync ? array_filter([
                                    'page' => request()->getScalar('page'),
                                    'sort' => request()->getScalar('sort'),
                                ]) : []
                        )
                    )
                ),
                name: static fn (mixed $item, ActionButtonContract $ctx): string => "$modalName-{$ctx->getData()?->getKey()}"
            )
            ->canSee(
                static fn (mixed $item, ?DataWrapperContract $data): bool => $data?->getKey()
                    && $resource->hasAction(Action::DELETE)
                    && $resource->setItem($item)->can(Ability::DELETE)
            )
            ->error()
            ->icon('trash')
            ->showInLine();
    }
}
