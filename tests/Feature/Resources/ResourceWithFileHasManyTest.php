<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use MoonShine\Fields\ID;
use MoonShine\Fields\Relationships\HasMany;
use MoonShine\Fields\Text;
use MoonShine\Pages\Crud\FormPage;
use MoonShine\Resources\ModelResource;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestFileResource;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;

uses()->group('resources-feature');
uses()->group('resources-has-many-files');

beforeEach(function (): void {

    $this->item = createItem(1, 1);

    $this->resource = TestResourceBuilder::new(Item::class)
        ->setTestFields([
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable(),
            HasMany::make('Files', 'itemFiles', resource: new TestFileResource())
        ])
    ;
});

it('resource with has many', function () {
    asAdmin()->get(
        to_page(page: FormPage::class, resource: $this->resource, params: ['resourceItem' => $this->item->id])
    )
        ->assertOk()
        ->assertSee('Name')
        ->assertSee('Files')
    ;
});

it('delete a has many file after delete item', function () {
    $file = addFile(new TestFileResource(), $this->item);

    $this->resource->setDeleteRelationships();

    deleteItem($this->resource, $this->item->getKey());

    Storage::disk('public')->assertMissing($file->hashName());

});

it('not delete a has many file after delete item', function () {
    $file = addFile(new TestFileResource(), $this->item);

    deleteItem($this->resource, $this->item->getKey());

    Storage::disk('public')->assertExists($file->hashName());
});

function addFile(ModelResource $resource, Model $item)
{
    $file = UploadedFile::fake()->create('test.csv');

    $data = [
        'path' => $file,
        'item_id' => $item->id,
    ];

    asAdmin()->post(
        $resource->route('crud.store', $item->getKey()),
        $data
    )
        ->assertRedirect();

    $item->refresh();

    expect($fileHasMany = $item->itemFiles()->first())
        ->not()->toBeNull()
        ->and($fileHasMany->path)
        ->toBe($file->hashName())
    ;

    Storage::disk('public')->assertExists($file->hashName());

    return $file;
}

function deleteItem(ModelResource $resource, int $itemId): void
{
    asAdmin()->delete(
        $resource->route('crud.destroy', $itemId),
    )
        ->assertRedirect()
    ;

    $item = Item::query()->where('id', $itemId)->first();

    expect($item)->toBeNull();
}
