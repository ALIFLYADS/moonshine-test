<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Components\Layout;

use Illuminate\Support\Collection;
use MoonShine\Core\Traits\WithCore;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\UI\Components\MoonShineComponent;

/**
 * @use WithCore<MoonShine>
 */
final class Locales extends MoonShineComponent
{
    protected string $view = 'moonshine::components.layout.locales';

    public string $current;

    public Collection $locales;

    public function __construct()
    {
        parent::__construct();

        $this->current = $this->getCore()->getConfig()->getLocale();
        $this->locales = Collection::make($this->getCore()->getConfig()->getLocales())
            ->mapWithKeys(fn (string $locale, int|string $code): array => [
                $this->getCore()->getRequest()->getUrlWithQuery([
                    $this->getCore()->getConfig()->getLocaleKey() => is_numeric($code) ? $locale : $code,
                ]) => $locale,
            ]);
    }
}
