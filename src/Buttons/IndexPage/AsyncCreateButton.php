<?php

namespace MoonShine\Buttons\IndexPage;

use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Resources\ModelResource;

final class AsyncCreateButton
{
    public static function for(ModelResource $resource, string $tableName = 'default'): ActionButton
    {
        return ActionButton::make(
            __('moonshine::ui.create'),
            to_page(
                page: $resource->formPage(),
                resource: $resource,
                params: ['_tableName' => $tableName],
                fragment: 'crud-form'
            )
        )
            ->primary()
            ->icon('heroicons.outline.plus')
            ->canSee(
                fn (?Model $item): bool => in_array('create', $resource->getActiveActions())
                && $resource->can('create')
            )
            ->inModal(
                fn (): array|string|null => __('moonshine::ui.create'),
                fn (): string => '',
                async: true
            );
    }
}
