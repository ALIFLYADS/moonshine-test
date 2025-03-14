<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChangeLocale
{
    final public const KEY = '_lang';

    public function handle(Request $request, Closure $next): Response
    {
        $key = moonshineConfig()->getLocaleKey();

        $locale = $request->input(
            $key,
            session($key, moonshineConfig()->getLocale())
        );

        $locale = strtolower((string) $locale);

        if (! \array_key_exists($locale, moonshineConfig()->getLocales())) {
            return $next($request);
        }

        if ($locale) {
            app()->setLocale($locale);
            moonshineConfig()->locale($locale);
        }

        if ($request->has($key)) {
            session()->put($key, $locale);
        }

        return $next($request);
    }
}
