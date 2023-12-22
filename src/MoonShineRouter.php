<?php

declare(strict_types=1);

namespace MoonShine;

use Closure;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Pages\Page;
use MoonShine\Pages\Pages;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class MoonShineRouter
{
    public static function to(string $name, array $params = []): string
    {
        return route(
            str($name)
                ->remove('moonshine.')
                ->prepend('moonshine.')
                ->value(),
            $params
        );
    }

    public static function asyncMethod(
        string $method,
        ?string $message = null,
        array $params = [],
        ?Page $page = null,
        ?ResourceContract $resource = null
    ): string {
        $resource ??= moonshineRequest()->getResource();
        $page ??= moonshineRequest()->getPage();

        return self::to('async.method', [
            'pageUri' => $page->uriKey(),
            'resourceUri' => $resource?->uriKey(),
            'method' => $method,
            'message' => $message,
            ...$params,
        ]);
    }

    public static function asyncTable(
        string $componentName = 'index-table'
    ): string {
        return self::to('async.table', [
            '_component_name' => $componentName,
            '_parentId' => moonshineRequest()->getParentResourceId(),
            'resourceUri' => moonshineRequest()->getResourceUri(),
            'pageUri' => moonshineRequest()->getPageUri(),
            'filters' => moonshineRequest()->get('filters'),
            'query-tag' => moonshineRequest()->get('query-tag'),
            'search' => moonshineRequest()->get('search'),
        ]);
    }

    public static function updateColumn(
        string $resourceUri,
        ?string $pageUri = null,
        ?string $relation = null,
    ): Closure {
        return static fn ($item): string => self::to(
            'column.' . ($relation ? 'relation' : 'resource') . '.update-column',
            array_filter([
                'resourceItem' => $item->getKey(),
                'resourceUri' => $resourceUri,
                'pageUri' => $pageUri,
                '_relation' => $relation,
            ])
        );
    }

    public static function home(): string
    {
        return self::to(
            config('moonshine.route.index', 'moonshine.index')
        );
    }

    public static function toRelation(
        string $action,
        int|string|null $resourceItem = null,
        ?string $relation = null,
        ?string $resourceUri = null,
        ?string $pageUri = null,
        ?string $parentField = null
    ): string {
        $data = [
            '_parent_field' => $parentField,
            '_relation' => $relation,
            'resourceItem' => $resourceItem,
        ];

        return moonshineRouter()->to("relation.$action", [
            'pageUri' => $pageUri ?? moonshineRequest()->getPageUri(),
            'resourceUri' => $resourceUri ?? moonshineRequest()->getResourceUri(),
            ...array_filter($data),
        ]);
    }

    public static function to_page(
        string|Page|null $page = null,
        string|ResourceContract|null $resource = null,
        array $params = [],
        bool $redirect = false,
        ?string $fragment = null,
    ): RedirectResponse|string {
        if ($fragment !== null && $fragment !== '') {
            $params += ['_fragment-load' => $fragment];
        }

        if (is_null($resource)) {
            $route = moonshine()->getPageFromUriKey(
                is_string($page) ? self::uriKey($page) : $page->uriKey()
            )->route($params);

            return $redirect
                ? redirect($route)
                : $route;
        }

        $resource = $resource instanceof ResourceContract
            ? $resource
            : new $resource();

        $route = $resource->getPages()
            ->when(
                is_null($page),
                static fn (Pages $pages) => $pages->first(),
                static fn (Pages $pages): ?Page => $pages->findByUri(
                    $page instanceof Page
                        ? $page->uriKey()
                        : self::uriKey($page)
                ),
            )->route($params);

        return $redirect
            ? redirect($route)
            : $route;
    }

    public static function uriKey(string $class): string
    {
        return str($class)
            ->classBasename()
            ->kebab()
            ->value();
    }
}
