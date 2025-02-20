<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Fields;

use Closure;

trait TabModeTrait
{
    protected bool $isTabMode = false;

    public function tabMode(Closure|bool|null $condition = null): static
    {
        $this->isTabMode = \is_null($condition) || value($condition, $this);

        return $this;
    }

    public function isTabMode(): bool
    {
        return $this->isTabMode;
    }
}