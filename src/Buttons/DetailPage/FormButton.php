<?php

namespace MoonShine\Buttons\DetailPage;

use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Pages\Crud\FormPage;
use MoonShine\Resources\ModelResource;

final class FormButton
{
    public static function for(ModelResource $resource): ActionButton
    {
        $ability = request('resourceItem') ? 'update' : 'create';

        return ActionButton::make(
            '',
            url: static fn (): string => to_page(
                page: FormPage::class,
                resource: $resource,
                params: ['resourceItem' => request('resourceItem')]
            )
        )
            ->canSee(
                fn (?Model $item) => ! is_null($item) && in_array($ability, $resource->getActiveActions())
                && $resource->setItem($item)->can($ability)
            )
            ->primary()
            ->icon('heroicons.outline.pencil')
            ->showInLine();
    }
}
