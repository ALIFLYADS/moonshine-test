<?php

namespace MoonShine\Pages\Crud;

use Illuminate\View\ComponentAttributeBag;
use MoonShine\Buttons\DetailPage\FormButton;
use MoonShine\Buttons\IndexPage\DeleteButton;
use MoonShine\Components\ActionGroup;
use MoonShine\Components\TableBuilder;
use MoonShine\Decorations\Block;
use MoonShine\Decorations\Divider;
use MoonShine\Decorations\Flex;
use MoonShine\Decorations\Fragment;
use MoonShine\Enums\PageType;
use MoonShine\Pages\Page;
use Throwable;

class DetailPage extends Page
{
    protected ?PageType $pageType = PageType::DETAIL;

    public function breadcrumbs(): array
    {
        $breadcrumbs = parent::breadcrumbs();

        $breadcrumbs[$this->route()] = $this->getResource()->getItem()
            ?->{$this->getResource()->column()};

        return $breadcrumbs;
    }

    /**
     * @throws Throwable
     */
    public function components(): array
    {
        if (is_null($this->getResource()->getItem())) {
            oops404();
        }

        return array_merge(
            $this->topLayer(),
            $this->mainLayer(),
            $this->bottomLayer(),
        );
    }

    protected function mainLayer(): array
    {
        $resource = $this->getResource();
        $item = $resource->getItem();

        return [
            Block::make([
                Fragment::make([
                    TableBuilder::make($resource->getDetailFields()->onlyFields())
                        ->cast($resource->getModelCast())
                        ->items([$item])
                        ->vertical()
                        ->simple()
                        ->preview()
                        ->tdAttributes(fn (
                            $data,
                            int $row,
                            int $cell,
                            ComponentAttributeBag $attributes
                        ): ComponentAttributeBag => $attributes->when(
                            $cell === 0,
                            fn (ComponentAttributeBag $attr): ComponentAttributeBag => $attr->merge([
                                'class' => 'font-semibold',
                                'width' => '20%',
                            ])
                        )),
                ])->withName('crud-show-table'),

                Divider::make(),

                Flex::make([
                    ActionGroup::make([
                        ...$resource->getDetailButtons(),
                        FormButton::for($resource),
                        DeleteButton::for($resource),
                    ])
                        ->setItem($item),
                ])->justifyAlign('end'),
            ]),
        ];
    }
}
