<?php

declare(strict_types=1);

namespace MoonShine\Resources;

use Illuminate\Validation\Rule;
use MoonShine\Attributes\Icon;
use MoonShine\Decorations\Block;
use MoonShine\Decorations\Heading;
use MoonShine\Decorations\Tab;
use MoonShine\Decorations\Tabs;
use MoonShine\Fields\Date;
use MoonShine\Fields\Email;
use MoonShine\Fields\ID;
use MoonShine\Fields\Image;
use MoonShine\Fields\Password;
use MoonShine\Fields\PasswordRepeat;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Text;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;

#[Icon('heroicons.outline.users')]
class MoonShineUserResource extends ModelResource
{
    public string $model = MoonshineUser::class;

    public string $title = 'MoonshineUsers';

    public string $column = 'name';

    public function fields(): array
    {
        return [
            Block::make([
                Tabs::make([
                    Tab::make('Main', [
                        ID::make()
                            ->sortable()
                            ->showOnExport(),

                        BelongsTo::make(
                            trans('moonshine::ui.resource.role'),
                            'moonshineUserRole',
                            static fn (MoonshineUserRole $model) => $model->name,
                            new MoonShineUserRoleResource(),
                        ),

                        Text::make(trans('moonshine::ui.resource.name'), 'name')
                            ->required()
                            ->showOnExport(),

                        Image::make(trans('moonshine::ui.resource.avatar'), 'avatar')
                            ->showOnExport()
                            ->disk(config('moonshine.disk', 'public'))
                            ->dir('moonshine_users')
                            ->allowedExtensions(['jpg', 'png', 'jpeg', 'gif']),

                        Date::make(trans('moonshine::ui.resource.created_at'), 'created_at')
                            ->format("d.m.Y")
                            ->default(now()->toDateTimeString())
                            ->sortable()
                            ->hideOnForm()
                            ->showOnExport(),

                        Email::make(trans('moonshine::ui.resource.email'), 'email')
                            ->sortable()
                            ->showOnExport()
                            ->required(),
                    ]),

                    Tab::make(trans('moonshine::ui.resource.password'), [
                        Heading::make('Change password'),

                        Password::make(trans('moonshine::ui.resource.password'), 'password')
                            ->customAttributes(['autocomplete' => 'new-password'])
                            ->hideOnIndex()
                            ->eye(),

                        PasswordRepeat::make(trans('moonshine::ui.resource.repeat_password'), 'password_repeat')
                            ->customAttributes(['autocomplete' => 'confirm-password'])
                            ->hideOnIndex()
                            ->eye(),
                    ]),
                ]),
            ]),
        ];
    }

    /**
     * @return array{name: string, moonshine_user_role_id: string, email: mixed[], password: string}
     */
    public function rules($item): array
    {
        return [
            'name' => 'required',
            'moonshine_user_role_id' => 'required',
            'email' => [
                'sometimes',
                'bail',
                'required',
                'email',
                Rule::unique('moonshine_users')->ignoreModel($item),
            ],
            'password' => $item->exists
                ? 'sometimes|nullable|min:6|required_with:password_repeat|same:password_repeat'
                : 'required|min:6|required_with:password_repeat|same:password_repeat',
        ];
    }

    public function search(): array
    {
        return ['id', 'name'];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [];
    }

    public function getBadge(): string
    {
        return (string) $this->getModel()->newModelQuery()->count();
    }
}
