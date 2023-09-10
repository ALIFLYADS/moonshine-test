<?php

declare(strict_types=1);

namespace MoonShine\Buttons\IndexPage;

use MoonShine\ActionButtons\ActionButton;
use MoonShine\Resources\ModelResource;

final class DetailButton
{
    public static function for(ModelResource $resource): ActionButton
    {
        return ActionButton::make(
            '',
            url: fn ($data): string => to_page(
                $resource,
                'detail-page',
                ['resourceItem' => $data->getKey()]
            )
        )
            ->icon('heroicons.outline.eye')
            ->showInLine();
    }
}
