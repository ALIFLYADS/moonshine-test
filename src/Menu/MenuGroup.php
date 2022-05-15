<?php

namespace Leeto\MoonShine\Menu;

use Illuminate\Support\Collection;
use Leeto\MoonShine\Exceptions\MenuException;
use Leeto\MoonShine\Resources\BaseResource;

class MenuGroup extends BaseMenuSection
{
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    final public function __construct(string $title, array $items, string $icon = null)
    {
        $this->title = $title;
        $this->items = collect($items)->map(function($item) {
            $item = is_string($item) ? new $item() : $item;

            throw_if(
                !$item instanceof MenuItem && !$item instanceof BaseResource,
                new MenuException('An object of the MenuItem|BaseResource class is required')
            );

            if($item instanceof BaseResource) {
                return new MenuItem($item->title(), $item);
            }

            return $item;
        });

        if($icon) {
            $this->icon($icon);
        }
    }
}