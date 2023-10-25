<?php

namespace MoonShine\Buttons\IndexPage;

use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Pages\Crud\FormPage;
use MoonShine\Resources\ModelResource;

final class CreateButton
{
    public static function forMode(ModelResource $resource, string $tableName = 'default'): ActionButton
    {
        return $resource->isCreateInModal()
            ? AsyncCreateButton::for($resource, $tableName)
            : self::for($resource);
    }

    public static function for(ModelResource $resource): ActionButton
    {
        return ActionButton::make(
            __('moonshine::ui.create'),
            to_page(
                page: FormPage::class,
                resource: $resource,
            )
        )
            ->primary()
            ->canSee(
                fn (?Model $item): bool => in_array('create', $resource->getActiveActions())
                && $resource->can('create')
            )
            ->icon('heroicons.outline.plus');
    }
}
