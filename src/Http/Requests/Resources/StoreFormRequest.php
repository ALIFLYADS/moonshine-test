<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Http\Requests\Resources;

use Leeto\MoonShine\MoonShineRequest;

class StoreFormRequest extends MoonShineRequest
{
    public function authorize(): bool
    {
        return $this->getResource()->can('create', $this->getModel());
    }

    public function rules(): array
    {
        return $this->getResource()->rules($this->getModel());
    }
}
