<?php

declare(strict_types=1);

namespace MoonShine\Buttons\IndexPage;

use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Components\FormBuilder;
use MoonShine\Resources\ModelResource;

final class DeleteButton
{
    public static function for(
        ModelResource $resource,
        string $tableName = 'default',
        string $redirectAfterDelete = '',
        bool $isAsync = false,
    ): ActionButton {
        $action = static fn (Model $data): string => route(
            'moonshine.crud.destroy',
            array_filter([
                'resourceUri' => $resource->uriKey(),
                'resourceItem' => $data->getKey(),
                ...$redirectAfterDelete
                    ? ['_redirect' => $redirectAfterDelete]
                    : [],
            ])
        );

        return ActionButton::make(
            '',
            url: $action
        )
            ->withConfirm(
                method: 'DELETE',
                formBuilder: fn (FormBuilder $formBuilder, Model $item) => $formBuilder->when(
                    $isAsync || $resource->isAsync(),
                    fn (FormBuilder $form): FormBuilder => $form->async(asyncEvents: 'table-updated-' . $tableName)
                )
            )
            ->canSee(
                fn (?Model $item): bool => ! is_null($item) && in_array('delete', $resource->getActiveActions())
                    && $resource->setItem($item)->can('delete')
            )
            ->secondary()
            ->icon('heroicons.outline.trash')
            ->showInLine();
    }
}
