<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Traits\Resource;

use MoonShine\Laravel\Enums\Action;
use MoonShine\Support\ListOf;

trait ResourceActions
{
    /**
     * @return ListOf<Action>
     */
    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [
            Action::CREATE,
            Action::VIEW,
            Action::UPDATE,
            Action::DELETE,
            Action::MASS_DELETE,
        ]);
    }

    /**
     * @return list<Action>
     */
    protected function getActiveActions(): array
    {
        return $this->activeActions()->toArray();
    }

    public function hasAction(Action ...$actions): bool
    {
        return collect($actions)->every(fn (Action $action): bool => \in_array($action, $this->getActiveActions()));
    }

    public function hasAnyAction(Action ...$actions): bool
    {
        return collect($actions)
            ->filter(fn (Action $action): bool => \in_array($action, $this->getActiveActions()))
            ->isNotEmpty();
    }
}
