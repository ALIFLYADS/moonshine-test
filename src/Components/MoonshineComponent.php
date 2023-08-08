<?php

declare(strict_types=1);

namespace MoonShine\Components;

use Stringable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\View\Component;
use MoonShine\Traits\Makeable;
use Throwable;

abstract class MoonshineComponent extends Component implements Stringable
{
    use Makeable;
    use Conditionable;
    use Macroable;

    /**
     * @throws Throwable
     */
    public function __toString(): string
    {
        return (string) $this->render();
    }
}