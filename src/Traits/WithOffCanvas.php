<?php

declare(strict_types=1);

namespace MoonShine\Traits;

use Closure;
use MoonShine\Components\ActionButtons\ActionButton;
use MoonShine\Components\Offcanvas;
use MoonShine\Enums\JsEvent;
use MoonShine\Support\AlpineJs;

trait WithOffCanvas
{
    protected ?Closure $offCanvas = null;

    public function isInOffCanvas(): bool
    {
        return ! is_null($this->offCanvas);
    }

    public function inOffCanvas(
        Closure|string|null $title = null,
        Closure|string|null $content = null,
        Closure|string|null $name = null,
        ?Closure $builder = null,
    ): static {
        if(is_null($name)) {
            $name = (string) spl_object_id($this);
        }

        $async = $this->purgeAsyncTap();

        $this->offCanvas = fn (mixed $data) => Offcanvas::make(
            title: fn () => value($title, $data, $this) ?? $this->getLabel(),
            content: fn () => value($content, $data, $this) ?? '',
            asyncUrl: $async ? $this->url($data) : null
        )
            ->name(value($name, $data, $this))
            ->when(
                ! is_null($builder),
                fn (Offcanvas $offCanvas) => $builder($offCanvas, $this)
            );

        return $this->onBeforeRender(
            static fn (ActionButton $btn): ActionButton => $btn->toggleOffCanvas(
                value($name, $btn->getItem(), $btn)
            )
        );
    }

    public function offCanvas(): ?Offcanvas
    {
        return value($this->offCanvas, $this->getItem(), $this);
    }

    public function toggleOffCanvas(string $name = 'default'): static
    {
        return $this->onClick(
            fn (): string => "\$dispatch('" . AlpineJs::event(JsEvent::OFF_CANVAS_TOGGLED, $name) . "')",
            'prevent'
        );
    }

    public function openOffCanvas(): static
    {
        return $this->onClick(fn (): string => 'toggleCanvas', 'prevent');
    }
}
