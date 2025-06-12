<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Resource;


use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\JsEvent;

/**
 * @mixin CrudResourceContract
 */
trait HasListComponent
{
    public function getListComponent(bool $withoutFragment = false): ?ComponentContract
    {
        return $this->getIndexPage()?->getListComponent($withoutFragment);
    }

    public function getListComponentName(): string
    {
        return rescue(
            fn(): string => $this->getIndexPage()?->getListComponentName(),
            "index-table-{$this->getUriKey()}",
            false,
        );
    }

    public function getListComponentNameWithRow(null|int|string $id = null): string
    {
        return rescue(
            fn(): string => $this->getIndexPage()?->getListComponentNameWithRow($id),
            $this->getListComponentName() . ($id ? "-$id" : "-{row-id}"),
            false,
        );
    }

    public function getListEventType(): JsEvent
    {
        return JsEvent::TABLE_UPDATED;
    }

    public function isListComponentRequest(): bool
    {
        return $this->getIndexPage()?->isListComponentRequest() === true;
    }

    public function getListEventName(?string $name = null, array $params = []): string
    {
        $name ??= $this->getListComponentName();

        return rescue(
            fn(): string => $this->getIndexPage()?->getListEventName($name, $params),
            AlpineJs::event($this->getListEventType(), $name, $params),
            false,
        );
    }
}
