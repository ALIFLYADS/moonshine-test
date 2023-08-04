<?php

namespace MoonShine\Pages;

use MoonShine\Casts\ModelCast;
use MoonShine\Components\FormBuilder;
use MoonShine\Decorations\TextBlock;
use MoonShine\Http\Controllers\ProfileController;

class ProfilePage extends Page
{
    public function components(): array
    {
        return [
            FormBuilder::make(action([ProfileController::class, 'store']))
                ->customAttributes([
                    'enctype' => 'multipart/form-data',
                ])
                ->fields(
                    $this->getResource()
                        ->getFields()
                        ->toArray()
                )
                ->cast(ModelCast::make(get_class($this->getResource()->getModel())))
                ->submit(__('moonshine::ui.save'), [
                    'class' => 'btn-lg btn-primary',
                ]),

            TextBlock::make(
                '',
                view('moonshine::ui.social-auth', [
                    'title' => trans('moonshine::ui.resource.link_socialite'),
                    'attached' => true,
                ])->render()
            ),
        ];
    }
}
