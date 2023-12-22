<?php

declare(strict_types=1);

namespace MoonShine\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Pages\Page;
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
        moonshine()
            ->resources($this->resources())
            ->pages($this->pages())
            ->init($this->menu());

        $theme = is_closure($this->theme())
            ? $this->theme()
            : fn (): array|Closure => $this->theme();

        moonshineColors()->lazyAssign($theme);
        moonshineAssets()->lazyAssign($theme);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * @return array<ResourceContract>
     */
    protected function resources(): array
    {
        return [];
    }

    /**
     * @return array<Page>
     */
    protected function pages(): array
    {
        return [];
    }

    /**
     * @return Closure|array<MenuElement>
     */
    protected function menu(): Closure|array
    {
        return [];
    }

    /**
     * @return Closure|array{css: string, colors: array, darkColors: array}
     */
    protected function theme(): Closure|array
    {
        return [];
    }
}
