<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Laravel\Handlers\Handler;
use MoonShine\Laravel\Handlers\Handlers;
use MoonShine\Support\ListOf;

/**
 * @mixin CrudPageContract
 */
trait HasHandlers
{
    /**
     * @return ListOf<Handler>
     */
    protected function handlers(): ListOf
    {
        return new ListOf(Handler::class, []);
    }

    public function hasHandlers(): bool
    {
        return $this->handlers()->toArray() !== [];
    }

    public function getHandlers(): Handlers
    {
        return Handlers::make($this->handlers()->toArray())
            ->each(fn(Handler $handler): Handler => $handler->setResource($this->getResource()));
    }
}
