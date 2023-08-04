<?php

declare(strict_types=1);

namespace MoonShine\Traits\Resource;

use Illuminate\Support\Facades\Route;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Http\Controllers\ActionController;
use MoonShine\MoonShineRouter;

/**
 * @mixin ResourceContract
 */
trait ResourceModelCrudRouter
{
    public string $pageAfterSave = 'index-page';

    public function resolveRoutes(): void
    {
        Route::prefix('resource')->group(function (): void {
            Route::controller(ActionController::class)
                ->prefix($this->uriKey())
                ->as("actions.")
                ->group(function (): void {

                });
        });
    }

    public function currentRoute(array $query = []): string
    {
        return str(request()->url())
            ->when(
                $query,
                static fn ($str) => $str->append('?')
                    ->append(Arr::query($query))
            )->value();
    }

    public function route(
        string $name = null,
        int|string $key = null,
        array $query = []
    ): string {
        if ($query === [] && cache()->has($this->queryCacheKey())) {
            parse_str(cache()->get($this->queryCacheKey(), ''), $query);
        }

        $query['resourceUri'] = $this->uriKey();

        unset($query['change-moonshine-locale'], $query['reset']);

        return MoonShineRouter::to(
            str($name)->contains('.') ? $name : 'crud.' . $name,
            $key ? array_merge(['resourceItem' => $key], $query) : $query
        );
    }

    public function getPageAfterSave(): string
    {
        return route('moonshine.page', [
            'resourceUri' => $this->uriKey(),
            'pageUri' => $this->pageAfterSave,
        ]);
    }
}
