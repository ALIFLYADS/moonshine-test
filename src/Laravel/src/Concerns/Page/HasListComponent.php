<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\JsEvent;

trait HasListComponent
{
    public function getListComponentName(): string
    {
        return "index-table-{$this->getResource()->getUriKey()}";
    }

    public function getListComponentNameWithRow(null|int|string $id = null): string
    {
        return $this->getListComponentName() . ($id ? "-$id" : "-{row-id}");
    }

    public function getListEventType(): JsEvent
    {
        return JsEvent::TABLE_UPDATED;
    }

    public function isListComponentRequest(): bool
    {
        return request()->ajax() && request()->getScalar('_component_name') === $this->getListComponentName();
    }

    public function getListEventName(?string $name = null, array $params = []): string
    {
        $name ??= $this->getListComponentName();

        return AlpineJs::event($this->getListEventType(), $name, $params);
    }

    public function getListComponent(bool $withoutFragment = false): ComponentContract
    {
        $items = $this->isLazy() ? [] : $this->getResource()->getItems();
        $fields = $this->getResource()->getIndexFields();

        $component = $this->getItemsComponent($items, $fields);

        if ($withoutFragment) {
            return $component;
        }

        return Fragment::make([$component])->name('crud-list');
    }
}
