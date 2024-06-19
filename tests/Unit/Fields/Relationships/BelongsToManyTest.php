<?php

declare(strict_types=1);

use MoonShine\Laravel\Contracts\Fields\HasAsyncSearch;
use MoonShine\Laravel\Contracts\Fields\HasPivot;
use MoonShine\Laravel\Contracts\Fields\HasRelatedValues;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestCategoryResource;
use MoonShine\UI\Contracts\Fields\HasFields;
use MoonShine\UI\Fields\Text;

uses()->group('model-relation-fields');
uses()->group('belongs-to-many-field');

beforeEach(function (): void {
    $this->item = Item::factory()
        ->hasCategories(5)
        ->create();

    $this->pivotFields = [
        Text::make('Pivot 1', 'pivot_1'),
        Text::make('Pivot 2', 'pivot_2'),
    ];

    $this->field = BelongsToMany::make('Categories', resource: new TestCategoryResource())
        ->fields($this->pivotFields)
        ->fillData($this->item);
});

describe('basic methods', function () {
    it('change preview', function () {
        expect($this->field->changePreview(static fn () => 'changed'))
            ->preview()
            ->toBe('changed');
    });

    it('formatted value', function () {
        $field = BelongsToMany::make('Categories', formatted: static fn () => 'changed', resource: new TestCategoryResource())
            ->fields($this->pivotFields)
            ->fillData($this->item);

        expect($field)
            ->toFormattedValue()
            ->toBeCollection()
            ->and($field->getValues()->toArray())
            ->each->toMatchArray(['label' => 'changed']);
    });

    it('default value', function () {
        $this->field->default(['default']);
    })->expectException(BadMethodCallException::class);

    it('applies', static function () {
        $field = BelongsToMany::make('Categories', resource: new TestCategoryResource());

        expect()
            ->applies($field);
    });
});

describe('common field methods', function () {
    it('names', function (): void {
        expect($this->field)
            ->getNameAttribute()
            ->toBe('categories[]')
            ->getNameAttribute('1')
            ->toBe('categories[1]');
    });

    it('correct interfaces', function (): void {
        expect($this->field)
            ->toBeInstanceOf(HasPivot::class)
            ->toBeInstanceOf(HasRelatedValues::class)
            ->toBeInstanceOf(HasFields::class)
            ->toBeInstanceOf(HasAsyncSearch::class);
    });

    it('type', function (): void {
        expect($this->field->attributes()->get('type'))
            ->toBeEmpty();
    });

    it('view', function (): void {
        expect($this->field->getView())
            ->toBe('moonshine::fields.relationships.belongs-to-many');
    });

    it('is group', function (): void {
        expect($this->field->isGroup())
            ->toBeTrue();
    });
});

describe('unique field methods', function () {

    it('async method', function (): void {
        expect($this->field)
            ->isAsyncSearch()
            ->toBeFalse()
            ->and($this->field->asyncSearch())
            ->isAsyncSearch()
            ->toBeTrue();
    });

    it('creatable method', function (): void {
        expect($this->field)
            ->isCreatable()
            ->toBeFalse()
            ->and($this->field->creatable())
            ->isCreatable()
            ->toBeTrue();
    });

    it('has fields', function (): void {
        expect($this->field->getFields())
            ->hasFields($this->pivotFields)
            ->each(static function ($field, $key): void {
                $key++;
                $field->toBeInstanceOf(Text::class)
                    ->getNameAttribute()
                    ->toBe('pivot_' . $key)
                    ->getIdentity()
                    ->toBe('pivot_' . $key);
            });
    });
});
