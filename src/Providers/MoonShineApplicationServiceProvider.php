<?php

declare(strict_types=1);

namespace MoonShine\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Menu\MenuManager;
use MoonShine\MoonShine;
use MoonShine\Utilities\AssetManager;
use Throwable;

class MoonShineApplicationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @throws Throwable
     */
    public function boot(): void
    {
        MoonShine::resources($this->resources());
        MoonShine::menu($this->menu());

        MenuManager::register(MoonShine::getMenu());

        MoonShine::resolveRoutes();

        $theme = $this->theme();

        moonshineAssets()->when(
            ! empty($theme['css']),
            static fn (AssetManager $assets): AssetManager => $assets->mainCss($theme['css'])
        )->when(
            ! empty($theme['colors']),
            static fn (AssetManager $assets): AssetManager => $assets->colors($theme['colors'])
        );
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * @return array<ResourceContract|string>
     */
    protected function resources(): array
    {
        return [];
    }

    /**
     * @return array<MenuElement>
     */
    protected function menu(): array
    {
        return [];
    }

    protected function theme(): array
    {
        return [];
    }
}
