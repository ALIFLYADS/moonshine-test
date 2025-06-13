<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Resource;

use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface HasCrudResponseModifiersContract
{
    public function modifyDestroyResponse(MoonShineJsonResponse $response): MoonShineJsonResponse;

    public function modifyMassDeleteResponse(MoonShineJsonResponse $response): MoonShineJsonResponse;

    public function modifySaveResponse(MoonShineJsonResponse $response): MoonShineJsonResponse;

    public function modifyErrorResponse(Response $response, Throwable $exception): Response;
}
