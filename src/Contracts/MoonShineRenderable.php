<?php

declare(strict_types=1);

namespace MoonShine\Contracts;

use Closure;
use Illuminate\Contracts\View\View;
use Stringable;

interface MoonShineRenderable extends Stringable
{
    public function render(): View|Closure|string;
}
