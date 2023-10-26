<?php

namespace MoonShine\Buttons\HasOneOrManyFields;

use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Components\FormBuilder;
use MoonShine\Decorations\TextBlock;
use MoonShine\Fields\Hidden;
use MoonShine\Fields\Relationships\HasMany;
use MoonShine\Resources\ModelResource;

final class HasManyDeleteButton
{
    public static function for(HasMany $field, ModelResource $resource, int|string $resourceItem): ActionButton
    {
        return ActionButton::make(
            '',
            url: static fn ($data): string => route(
                'moonshine.crud.destroy',
                [
                    'resourceUri' => $resource->uriKey(),
                    'resourceItem' => $data->getKey(),
                    '_redirect' => to_page(
                        page: $resource->formPage(),
                        resource: moonshineRequest()->getResource(),
                        params: ['resourceItem' => $resourceItem]
                    ),
                ]
            )
        )
            ->secondary()
            ->icon('heroicons.outline.trash')
            ->inModal(
                fn (): array|string|null => __('moonshine::ui.delete'),
                fn (ActionButton $action): string => (string) form(
                    $action->url(),
                    fields: [
                        Hidden::make('_method')->setValue('DELETE'),
                        TextBlock::make('', __('moonshine::ui.confirm_message')),
                    ]
                )
                    ->when(
                        $field->isAsync() || $resource->isAsync(),
                        fn (FormBuilder $form): FormBuilder => $form
                            ->async(asyncEvents: 'table-updated-' . $field->getRelationName())
                    )
                    ->submit(__('moonshine::ui.delete'), ['class' => 'btn-secondary'])
            )
            ->canSee(
                fn (?Model $item): bool => ! is_null($item) && in_array('delete', $resource->getActiveActions())
                    && $resource->setItem($item)->can('delete')
            )
            ->showInLine();
    }
}
