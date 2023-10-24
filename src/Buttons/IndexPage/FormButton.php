<?php

declare(strict_types=1);

namespace MoonShine\Buttons\IndexPage;

use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Pages\Crud\FormPage;
use MoonShine\Resources\ModelResource;

final class FormButton
{
    public static function forMode(ModelResource $resource): ActionButton
    {
        return $resource->isEditInModal()
            ? AsyncEditButton::for($resource)
            : FormButton::for($resource);
    }

    public static function for(ModelResource $resource): ActionButton
    {
        return ActionButton::make(
            '',
            url: static fn ($data): string => to_page(
                page: FormPage::class,
                resource: $resource,
                params: ['resourceItem' => $data->getKey()]
            )
        )
            ->primary()
            ->icon('heroicons.outline.pencil')
            ->canSee(
                fn (?Model $item): bool => ! is_null($item) && in_array('update', $resource->getActiveActions())
                && $resource->setItem($item)->can('update')
            )
            ->customAttributes(['class' => 'edit-button'])
            ->showInLine();
    }
}
