<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Pages;

use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends Page<null>
 */
class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Dashboard';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [];
    }
}
