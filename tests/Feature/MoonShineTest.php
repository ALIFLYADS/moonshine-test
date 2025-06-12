<?php

declare(strict_types=1);

use MoonShine\Support\Enums\PageType;

uses()->group('core');

it('recognizes internal request as MoonShine request', function (): void {

    $resource = $this->moonShineUserResource();

    asAdmin()
        ->get($resource->getIndexPageUrl())
        ->assertOk();

    expect(moonshineRequest()->isMoonShineRequest())
        ->toBeTrue();

});

it('recognizes external request as non MoonShine request', function (): void {
    $this->get('/')->assertValid();

    expect(moonshineRequest()->isMoonShineRequest())
        ->toBeFalse();
});
