<?php

declare(strict_types=1);

use MoonShine\Core\TypeCasts\MixedDataWrapper;
use MoonShine\Tests\Fixtures\Resources\TestInMemoryResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

uses()->group('resources');
uses()->group('crud-resources');

it('successful save', function () {
    $resource = app(TestInMemoryResource::class);
    $resource->fields = [
        ID::make(),
        Text::make('Title'),
    ];
    $prev = new MixedDataWrapper(['id' => 1, 'title' => 'prev'], 1);

    $resource->items = [
        $prev
    ];

    fakeRequest(parameters: [
        'id' => $prev->getKey(),
        'title' => 'new'
    ]);

    expect($resource->save($prev)->toArray())
        ->toBe([
            'id' => '1',
            'title' => 'new'
        ]);
});

it('successful find', function () {
    $resource = app(TestInMemoryResource::class);
    $resource->items = [
        ['id' => 1, 'title' => '1'],
        ['id' => 2, 'title' => '2'],
        ['id' => 3, 'title' => '3'],
    ];

    $resource->setItemID(2);

    $item = $resource->findItem();

    expect($item->getKey())->toBe(2);
});

it('exception find', function () {
    $resource = app(TestInMemoryResource::class);
    $resource->items = [
        ['id' => 1, 'title' => '1'],
        ['id' => 2, 'title' => '2'],
        ['id' => 3, 'title' => '3'],
    ];

    $resource->setItemID(4);

    $item = $resource->findItem();

    expect($item->getKey())->toBeNull();

    $resource->setItemID(4);

    $resource->findItem(true);
})->throws(Exception::class, 'Item not found');

it('successful delete', function () {
    $resource = app(TestInMemoryResource::class);
    $resource->fields = [
        ID::make(),
        Text::make('Title'),
    ];

    $resource->items = [
        ['id' => 1, 'title' => 'test'],
        ['id' => 2, 'title' => 'test'],
        ['id' => 3, 'title' => 'test'],
    ];

    expect($resource->getItems())->toHaveCount(3);

    $resource->delete(new MixedDataWrapper(['id' => 2, 'title' => 'test'], 2));

    expect($resource->getItems())->toHaveCount(2);
});

it('successful mass delete', function () {
    $resource = app(TestInMemoryResource::class);
    $resource->fields = [
        ID::make(),
        Text::make('Title'),
    ];

    $resource->items = [
        ['id' => 1, 'title' => 'test'],
        ['id' => 2, 'title' => 'test'],
        ['id' => 3, 'title' => 'test'],
    ];

    expect($resource->getItems())->toHaveCount(3);

    $resource->massDelete([1, 3]);

    expect($resource->getItems())->toHaveCount(1);
});
