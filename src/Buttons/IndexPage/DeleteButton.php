<?php

declare(strict_types=1);

namespace MoonShine\Buttons\IndexPage;

use MoonShine\ActionButtons\ActionButton;
use MoonShine\Decorations\TextBlock;
use MoonShine\Fields\Hidden;
use MoonShine\Resources\ModelResource;

final class DeleteButton
{
    public static function for(ModelResource $resource, string $redirectAfterDelete = ''): ActionButton
    {
        return ActionButton::make(
            '',
            url: fn ($data): string => route(
                'moonshine.crud.destroy',
                $redirectAfterDelete ?
                [
                    'resourceUri' => $resource->uriKey(),
                    'resourceItem' => $data->getKey(),
                    '_redirect' => $redirectAfterDelete,
                ] :
                [
                    'resourceUri' => $resource->uriKey(),
                    'resourceItem' => $data->getKey(),
                ]
            )
        )
            ->customAttributes(['class' => 'btn-pink'])
            ->icon('heroicons.outline.trash')
            ->inModal(
                fn (): array|string|null => __('moonshine::ui.delete'),
                fn (ActionButton $action): string => (string) form(
                    $action->url(),
                    fields: [
                        Hidden::make('_method')->setValue('DELETE'),
                        TextBlock::make('', __('moonshine::ui.confirm_message')),
                    ]
                )->submit(__('moonshine::ui.delete'), ['class' => 'btn-pink'])
            )
            ->showInLine();
    }
}
