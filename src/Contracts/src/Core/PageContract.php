<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use Illuminate\Contracts\Support\Renderable;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use MoonShine\Contracts\MenuManager\MenuFillerContract;
use MoonShine\Contracts\UI\LayoutContract;
use MoonShine\Support\Enums\PageType;

/**
 * @template-covariant  I of PageContract

 * @mixin I
 */
interface PageContract extends
    HasCoreContract,
    HasComponentsContract,
    HasResourceContract,
    MenuFillerContract,
    HasAssetsContract,
    Renderable,
    HasUriKeyContract,
    HasStructureContract,
    StatefulContract
{
    public function getPageType(): ?PageType;

    public function getLayout(): LayoutContract;

    public function getRouter(): RouterContract;

    public function getRoute(array $params = []): string;

    public function getTitle(): string;

    public function getBreadcrumbs(): array;

    public function getSubtitle(): string;

    public function isCheckUrl(): bool;

    public function checkUrl(): static;

    public function loaded(): static;
}
