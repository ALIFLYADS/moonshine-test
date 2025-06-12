<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use MoonShine\Contracts\MenuManager\MenuFillerContract;

/**
 * @template TPage of PageContract = PageContract
 */
interface ResourceContract extends
    HasCoreContract,
    MenuFillerContract,
    HasAssetsContract,
    HasUriKeyContract,
    StatefulContract
{
    /**
     * @return PagesContract<TPage>
     */
    public function getPages(): PagesContract;

    public function getRouter(): RouterContract;

    public function getTitle(): string;

    public function booted(): static;

    public function loaded(): static;
}
