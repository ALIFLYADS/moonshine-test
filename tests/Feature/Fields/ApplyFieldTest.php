<?php

declare(strict_types=1);

use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;
use MoonShine\Tests\Fixtures\Resources\TestCategoryResource;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Text;

uses()->group('fields');

beforeEach(function (): void {
    $this->fields = Fields::make([
        Text::make('Title'),
        Json::make('Data')->fields([
            Text::make('Title'),
        ]),
    ]);

    $this->item = MoonshineUser::factory()->createOne();

    $this->relationFields = Fields::make([
        Email::make('Email', 'email'),
        BelongsTo::make('Role', 'moonshineUserRole', resource: MoonShineUserRoleResource::class),
    ]);
});

it('primitive values', function (): void {
    $data = [];
    $requestData = [
        'title' => 'Hello world',
        'data' => [
            ['title' => 'Inner Hello world']
        ]
    ];

    fakeRequest(parameters: $requestData);

    $data = Field::silentApply(function () use (&$data) {
        $this->fields->onlyFields()->each(function (FieldContract $field) use (&$data): void {
            $data = $field->apply(fn($d, $v, FieldContract $ctx) => data_set($d, $ctx->getColumn(), $v), $data);
        });

        return $data;
    });


    expect($data)->toBe($requestData);
});

it('relation belongsTo values', function (): void {
    $data = $this->item;
    $requestData = [
        'email' => 'test@example.com',
        'moonshine_user_role_id' => '10',
    ];

    fakeRequest(parameters: $requestData);

    $data = Field::silentApply(function () use (&$data) {
        $this->relationFields->onlyFields()->each(function (FieldContract $field) use (&$data): void {
            $field->apply(fn($d, $v, FieldContract $ctx) => data_set($d, $ctx->getColumn(), $v), $data);
        });

        return $data;
    });

    expect($data->only('email', 'moonshine_user_role_id'))->toBe($requestData);
});

it('relation belongsToMany simple values', function (): void {
    $data = createItem();
    $fields = Fields::make([
        Text::make('Name'),
        BelongsToMany::make('Categories', resource: TestCategoryResource::class)
            ->selectMode(),
    ]);
    $requestData = [
        'name' => 'Danil',
        'categories' => ['1','2','3','4','5'],
    ];

    fakeRequest(parameters: $requestData);

    $data = Field::silentApply(function () use (&$data, $fields) {
        $fields->onlyFields()->each(function (FieldContract $field) use (&$data): void {
            $data = $field->beforeApply($data);
            $data = $field->apply(fn($d, $v, FieldContract $ctx) => data_set($d, $ctx->getColumn(), $v), $data);
            $data = $field->afterApply($data);
        });

        return $data;
    });

    $result = $data->only('name', 'categories');
    $result['categories'] = $result['categories']->toArray();

    expect($result)->toBe($requestData);
});

it('relation belongsToMany default values', function (): void {
    $data = createItem();
    $fields = Fields::make([
        Text::make('Name'),
        BelongsToMany::make('Categories', resource: TestCategoryResource::class)
            ->fields([
                Text::make('pivot_1', 'pivot_1'),
                Text::make('pivot_2', 'pivot_2'),
                Text::make('pivot_3', 'pivot_3'),
            ]),
    ]);
    $requestData = [
        'name' => 'Danil',
        'categories' => ['1' => '1','2' => '2'],
        'categories_pivot' => [
            '1' => [
                'pivot' => [
                    'pivot_1' => '1',
                    'pivot_2' => '2',
                    'pivot_3' => '3',
                ]
            ],
            '2' => [
                'pivot' => [
                    'pivot_1' => '4',
                    'pivot_2' => '5',
                    'pivot_3' => '6',
                ]
            ]
        ],
    ];

    fakeRequest(parameters: $requestData);

    $data = Field::silentApply(function () use (&$data, $fields) {
        $fields->onlyFields()->each(function (FieldContract $field) use (&$data): void {
            $data = $field->beforeApply($data);
            $data = $field->apply(fn($d, $v, FieldContract $ctx) => data_set($d, $ctx->getColumn(), $v), $data);
            $data = $field->afterApply($data);
        });

        return $data;
    });

    $result = $data->only('name', 'categories');

    expect($result)->toBe([
        'name' => 'Danil',
        'categories' => [
            '1' => [
                'pivot_1' => '1',
                'pivot_2' => '2',
                'pivot_3' => '3',
            ],
            '2' => [
                'pivot_1' => '4',
                'pivot_2' => '5',
                'pivot_3' => '6',
            ]
        ]
    ]);
});

