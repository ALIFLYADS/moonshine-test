<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Resource;


use Symfony\Component\HttpFoundation\Response;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use Throwable;

trait HasCrudResponseModifiers
{
    public function modifyDestroyResponse(MoonShineJsonResponse $response): MoonShineJsonResponse
    {
        return $response;
    }

    public function modifyMassDeleteResponse(MoonShineJsonResponse $response): MoonShineJsonResponse
    {
        return $response;
    }

    public function modifySaveResponse(MoonShineJsonResponse $response): MoonShineJsonResponse
    {
        return $response;
    }

    public function modifyErrorResponse(Response $response, Throwable $exception): Response
    {
        return $response;
    }
}
