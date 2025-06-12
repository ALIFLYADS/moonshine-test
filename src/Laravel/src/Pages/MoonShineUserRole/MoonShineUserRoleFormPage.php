<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Pages\MoonShineUserRole;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<MoonshineUserRole, MoonShineUserRoleResource>
 */
final class MoonShineUserRoleFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Text::make(__('moonshine::ui.resource.role_name'), 'name')
                    ->required(),
            ]),
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'name' => ['required', 'min:5'],
        ];
    }
}
