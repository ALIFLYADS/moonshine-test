<?php

declare(strict_types=1);

namespace MoonShine\Http\Requests\Resources;

use MoonShine\Http\Requests\MoonshineFormRequest;

final class MassDeleteFormRequest extends MoonshineFormRequest
{
    public function authorize(): bool
    {
        return $this->getResource()->can('massDelete');
    }

    /**
     * @return array{ids: string[]}
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
        ];
    }
}
