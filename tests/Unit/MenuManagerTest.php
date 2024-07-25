<?php

declare(strict_types=1);

use MoonShine\Contracts\MenuManager\MenuManagerContract;
use MoonShine\MenuManager\MenuElements;
use MoonShine\MenuManager\MenuItem;
use MoonShine\MenuManager\MenuManager;
use MoonShine\Tests\Fixtures\Resources\TestCommentResource;
use MoonShine\Tests\Fixtures\Resources\TestImageResource;
use Pest\Expectation;

uses()->group('menu-manager');

beforeEach(function () {
    $this->menuManager = $this->moonshineCore->getContainer(MenuManagerContract::class);
});

it('empty menu elements', function (): void {
    expect($this->menuManager->all())
        ->toBeInstanceOf(MenuElements::class)
        ->toBeEmpty();
});

it('add menu elements', function (): void {
    $this->menuManager->add(MenuItem::make('Item', '/'));

    expect($this->menuManager->all())
        ->toHaveCount(1)
        ->each(
            static fn (Expectation $item) => $item
            ->getUrl()
            ->toBe('/')
            ->getLabel()
            ->toBe('Item')
        );
});

it('add before menu elements', function (): void {
    $this->menuManager->add([
        MenuItem::make('Item 1', '/item1'),
        MenuItem::make('Item 2', '/item2'),
        MenuItem::make('Item 3', '/item3'),
    ]);

    $item = MenuItem::make('Item 2_0', '/item2_0');

    $this->menuManager->addBefore(static fn (MenuItem $el) => $el->getUrl() === '/item2', $item);

    $items = $this->menuManager->all();

    expect($items[1])
        ->toBe($item);
});

it('add after menu elements', function (): void {
    $this->menuManager->add([
        MenuItem::make('Item 1', '/item1'),
        MenuItem::make('Item 2', '/item2'),
        MenuItem::make('Item 3', '/item3'),
    ]);

    $item = MenuItem::make('Item 2_0', '/item2_0');

    $this->menuManager->addAfter(static fn (MenuItem $el) => $el->getUrl() === '/item2', [$item]);

    $items = $this->menuManager->all();

    expect($items[2])
        ->toBe($item);
});

it('remove menu elements', function (): void {
    $this->menuManager->add([
        MenuItem::make('Item 1', '/item1'),
        MenuItem::make('Item 2', '/item2'),
        MenuItem::make('Item 3', '/item3'),
    ]);

    $this->menuManager->remove(static fn (MenuItem $el) => $el->getUrl() === '/item2');

    $items = $this->menuManager->all();

    expect($items)
        ->toHaveCount(2);
});


it('replace items', function (): void {
    $this->menuManager->add([
        MenuItem::make('Item 1', '/item1'),
        MenuItem::make('Item 2', '/item2'),
        MenuItem::make('Item 3', '/item3'),
    ]);

    expect($this->menuManager->all())
        ->toHaveCount(3)
        ->and($this->menuManager->all([MenuItem::make('Item 1', '/item1')]))
        ->toHaveCount(1);
});

it('only visible items', function (): void {
    $this->menuManager->add([
        MenuItem::make('Item 1', '/item1'),
        MenuItem::make('Item 2', '/item2')->canSee(static fn () => false),
        MenuItem::make('Item 3', '/item3'),
    ]);

    expect($this->menuManager->all())
        ->toHaveCount(2);
});


it('check active element', function (): void {
    fakeRequest('/item2');

    $menuElements = app(MenuManager::class)->add([
        MenuItem::make('Item 1', '/item1'),
        MenuItem::make('Item 2', '/item2'),
        MenuItem::make('Item 2', '/item2?query=1'),
    ])->all();

    expect($menuElements[0]->isActive())
        ->toBeFalse()
        ->and($menuElements[1]->isActive())
        ->toBeTrue()
        ->and($menuElements[2]->isActive())
        ->toBeTrue();

    $menuElements = app(MenuManager::class)->add([
        MenuItem::make('Item 1', 'http://localhost/item1'),
        MenuItem::make('Item 2', 'http://localhost/item2'),
        MenuItem::make('Item 2', 'http://localhost/item2?query=1'),
    ])->all();

    expect($menuElements[0]->isActive())
        ->toBeFalse()
        ->and($menuElements[1]->isActive())
        ->toBeTrue()
        ->and($menuElements[2]->isActive())
        ->toBeTrue();

    $menuElements = app(MenuManager::class)->add([
        MenuItem::make('Item 1', 'http://localhost/item1')->whenActive(static fn () => true),
        MenuItem::make('Item 2', 'http://localhost/item2'),
        MenuItem::make('Item 3', 'http://localhost/item2?query=1'),
    ])->all();

    expect($menuElements[0]->isActive())
        ->toBeTrue()
        ->and($menuElements[1]->isActive())
        ->toBeTrue()
        ->and($menuElements[2]->isActive())
        ->toBeTrue();

    fakeRequest('/admin/resource/test-image-resource/index-page?resourceItem=1');

    $menuElements = app(MenuManager::class)->add([
        MenuItem::make('Item 1', TestCommentResource::class),
        MenuItem::make('Item 2', TestImageResource::class),
    ])->all();

    expect($menuElements[0]->isActive())
        ->toBeFalse()
        ->and($menuElements[1]->isActive())
        ->toBeTrue();
});
