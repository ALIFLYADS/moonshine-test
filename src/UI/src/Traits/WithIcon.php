<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits;

use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Icon;

trait WithIcon
{
    protected ?string $icon = null;

    protected bool $customIcon = false;

    protected ?string $iconPath = null;

    /**
     * @param  non-empty-string  $icon
     */
    public function icon(string $icon, bool $custom = false, ?string $path = null): static
    {
        $this->icon = $icon;
        $this->customIcon = $custom;
        $this->iconPath = $path;

        return $this;
    }

    /**
     * @param  int  $size
     * @param  Color|string  $color
     * @param  string[]  $attributes
     *
     * @return string
     */
    public function getIcon(
        int $size = 8,
        Color|string $color = '',
        array $attributes = []
    ): string {
        if ($this->getIconValue() === '') {
            return '';
        }

        $icon = Icon::make(
            $this->getIconValue(),
            $size,
            $color,
            $this->getIconPath()
        )->customAttributes($attributes);

        if ($this->isCustomIcon()) {
            $icon->custom();
        }

        return (string) rescue(
            static fn () => $icon->render(),
            rescue: static fn (): string => '',
            report: false
        );
    }

    public function isCustomIcon(): bool
    {
        return $this->customIcon;
    }

    public function getIconPath(): ?string
    {
        return $this->iconPath;
    }

    public function getIconValue(): string
    {
        return $this->icon ?? '';
    }
}
