<?php

declare(strict_types=1);

namespace MoonShine\Pages;

use Illuminate\Support\Collection;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Enums\PageType;

/**
 * @template TKey of array-key
 *
 * @extends Collection<TKey, Page>
 */
final class Pages extends Collection
{
    public function setResource(ResourceContract $resource): Pages
    {
        return $this->each(fn (Page $page): Page => $page->setResource($resource));
    }

    public function getPageForType(PageType $pageType): ?Page
    {
        return $this->first(fn(Page $page) => $page->pageType() === $pageType);
    }

    public function form(): ?Page
    {
        return $this->getPageForType(PageType::FORM);
    }

    public function detail(): ?Page
    {
        return $this->getPageForType(PageType::DETAIL);
    }

    public function findByUri(
        string $uri,
        Page $default = null
    ): ?Page {
        return $this->first(
            function (Page $page) use ($uri): bool {
                if($page->uriKey() === $uri) {
                    return true;
                }

                return
                    ! is_null($pageTypeUri = PageType::getTypeFromUri($uri))
                    && $page->pageType() === $pageTypeUri
                ;
            },
            $default
        );
    }
}
