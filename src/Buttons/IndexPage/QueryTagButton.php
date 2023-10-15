<?php

namespace MoonShine\Buttons\IndexPage;

use MoonShine\ActionButtons\ActionButton;
use MoonShine\Pages\Crud\IndexPage;
use MoonShine\QueryTags\QueryTag;
use MoonShine\Resources\ModelResource;

final class QueryTagButton
{
    public static function for(ModelResource $resource, QueryTag $tag): ActionButton
    {
        return ActionButton::make(
            $tag->label(),
            to_page(page: IndexPage::class, resource: $resource, params: ['query-tag' => $tag->uri()])
        )
            ->showInLine()
            ->icon($tag->iconValue())
            ->canSee(fn (): bool => $tag->isSee(moonshineRequest()))
            ->when(
                $tag->isActive(),
                fn (ActionButton $btn): ActionButton => $btn->primary()
            );
    }
}
