<?php

declare(strict_types=1);

use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Buttons\CreateButton;
use MoonShine\Laravel\Buttons\MassDeleteButton;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\Tests\Fixtures\Models\Comment;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestCommentResource;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

uses()->group('resources-feature');
uses()->group('resources-policies');

beforeEach(function (): void {

    $this->item = createItem(1, 1);

    $this->resource = TestResourceBuilder::new(Item::class)
        ->setTestFields([
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable(),
            HasMany::make(
                'Comments',
                resource: app(TestCommentResource::class)->setTestPolicy(true)
            )->creatable(),
        ])
        ->setTestPolicy(true)
    ;
});

it('policies in index', function () {
    expect($this->resource->isWithPolicy())
        ->toBeTrue();

    asAdmin()->get(
        $this->resource->getIndexPageUrl()
    )
        ->assertOk()
        ->assertSeeHtml(CreateButton::for($this->resource))
        ->assertSeeHtml(MassDeleteButton::for($this->resource))
    ;

});

it('policy in has many', function () {
    $comment = Comment::query()->first();

    asAdmin()->get(
        $this->resource->getFormPageUrl($this->item->id)
    )
        ->assertOk()
        ->assertSee($comment->content)
        ->assertDontSee('has-many-modal-comments-create')
        ->assertSee('has-many-modal-mass-delete')
    ;

});

it('policies index forbidden', function () {
    MoonshineUser::query()->where('id', 1)->update([
        'name' => 'Policies test',
    ]);

    asAdmin()->get(
        $this->resource->getIndexPageUrl()
    )->assertForbidden();
});

it('policies in detail', function () {
    asAdmin()->get(
        $this->resource->getDetailPageUrl($this->item->id)
    )->assertForbidden();
});
