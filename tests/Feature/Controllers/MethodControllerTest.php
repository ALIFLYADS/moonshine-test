<?php

declare(strict_types=1);

uses()->group('method-controller');

it('get response', function () {
    asAdmin()->get($this->moonshineCore->getRouter()->to('method', [
        'method' => 'testAsyncMethod',
        'resourceUri' => 'test-item-resource',
        'pageUri' => 'index-page',
        'var' => 'foo',
    ]))
        ->assertJson(['var' => 'foo'])
        ->assertOk()
    ;
});

it('get error response', function () {
    asAdmin()->get($this->moonshineCore->getRouter()->to('method', [
        'method' => 'testAsyncMethod2',
        'resourceUri' => 'test-item-resource',
        'pageUri' => 'index-page',
        'var' => 'foo',
    ]))
        ->assertServerError()
        ->assertJson([
            'message' => 'testAsyncMethod2 does not exist',
            'messageType' => 'error',
        ])
    ;
});
