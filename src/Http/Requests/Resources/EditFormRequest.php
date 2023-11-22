<?php

declare(strict_types=1);

namespace MoonShine\Http\Requests\Resources;

use MoonShine\Http\Requests\MoonShineFormRequest;

final class EditFormRequest extends MoonShineFormRequest
{
    public function authorize(): bool
    {
        if (! in_array(
            'update',
            $this->getResource()->getActiveActions(),
            true
        )) {
            return false;
        }

        return $this->getResource()->can('update');
    }

    public function rules(): array
    {
        return [];
    }
}
