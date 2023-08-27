<?php

namespace MoonShine\Pages\Crud;

use MoonShine\Decorations\Divider;
use MoonShine\Decorations\Fragment;
use MoonShine\Fields\Fields;
use MoonShine\Fields\Hidden;
use MoonShine\Pages\Page;

class FormPage extends Page
{
    public function breadcrumbs(): array
    {
        $breadcrumbs = parent::breadcrumbs();

        if ($this->getResource()->getItemID()) {
            $breadcrumbs[$this->route()] = $this->getResource()
                ?->getItem()
                ?->{$this->getResource()->column()};
        } else {
            $breadcrumbs[$this->route()] = __('moonshine::ui.add');
        }

        return $breadcrumbs;
    }

    public function components(): array
    {
        $resource = $this->getResource();

        $item = $resource->getItem();

        $action = $this->getResource()->route(
            is_null($item) ? 'crud.store' : 'crud.update',
            $item?->getKey()
        );

        $components = [];

        $components[] = Fragment::make([
            form($action)
                ->fields(
                    $resource
                        ->getFormFields()
                        ->when(
                            ! is_null($item),
                            fn (Fields $fields): Fields => $fields->push(
                                Hidden::make('_method')->setValue('PUT')
                            )
                        )
                        ->toArray()
                )
                ->name('crud')
                ->fill($item?->attributesToArray() ?? [])
                ->cast($resource->getModelCast())
                ->submit(__('moonshine::ui.save'), ['class' => 'btn-primary btn-lg']),
        ])->withName('form');

        foreach ($resource->getOutsideFields() as $field) {
            $components[] = Divider::make($field->label());
            $components[] = $field->resolveFill(
                $item?->attributesToArray() ?? [],
                $item
            )->value(withOld: false);
        }

        return $components;
    }
}
