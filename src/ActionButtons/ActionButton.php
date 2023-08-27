<?php

declare(strict_types=1);

namespace MoonShine\ActionButtons;

use Closure;
use MoonShine\Contracts\Actions\ActionButtonContract;

/**
 * @method static static make(Closure|string $label, Closure|string|null $url = null, mixed $item = null)
 */
class ActionButton extends AbstractAction implements ActionButtonContract
{
    protected bool $isBulk = false;

    public function __construct(
        Closure|string $label,
        protected Closure|string|null $url = null,
        protected mixed $item = null
    ) {
        $this->setLabel($label);
    }

    public function bulk(): self
    {
        $this->isBulk = true;

        return $this;
    }

    public function isBulk(): bool
    {
        return $this->isBulk;
    }

    public function getItem(): mixed
    {
        return $this->item;
    }

    public function setItem(mixed $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function onClick(Closure $onClick, ?string $modifier = null): self
    {
        $event = 'x-on:click';

        if (! is_null($modifier)) {
            $event .= ".$modifier";
        }

        $this->customAttributes([
            $event => $onClick($this->getItem()),
        ]);

        return $this;
    }

    public function url(): string
    {
        return is_closure($this->url)
            ? call_user_func($this->url, $this->getItem())
            : $this->url;
    }
}
