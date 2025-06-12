<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Http\Requests\Resources;

use MoonShine\Contracts\Core\PageContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Enums\Ability;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Http\Requests\MoonShineFormRequest;
use MoonShine\Laravel\Resources\CrudResource;
use Throwable;

final class StoreFormRequest extends MoonShineFormRequest
{
    /**
     * @throws ResourceException
     * @throws Throwable
     */
    public function authorize(): bool
    {
        $this->beforeResourceAuthorization();

        $resource = $this->getResource();

        if (\is_null($resource)) {
            return false;
        }

        if (! $resource->hasAction(Action::CREATE)) {
            return false;
        }

        return $resource->can(Ability::CREATE);
    }

    public function rules(): array
    {
        if (! $this->getResource() instanceof CrudResource || ! $this->getResource()->getFormPage() instanceof PageContract) {
            return [];
        }

        return $this->getResource()->getFormPage()->getRules();
    }
}
