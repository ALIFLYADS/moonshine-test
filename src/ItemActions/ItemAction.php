<?php

declare(strict_types=1);

namespace Leeto\MoonShine\ItemActions;

use Illuminate\Database\Eloquent\Model;
use Leeto\MoonShine\Traits\Makeable;
use Closure;

final class ItemAction
{
    use Makeable;

    public function __construct(
        protected string $label,
        protected Closure $callback,
        protected string $message = 'Done',
    )
    {

    }

    public function label(): string
    {
        return $this->label;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function callback(Model $model): mixed
    {
        return call_user_func($this->callback, $model);
    }
}
