<?php

namespace Leeto\MoonShine\Contracts\Fields\Relationships;

use Illuminate\Database\Eloquent\Model;

interface HasRelationshipContract
{
    public function relatedValues(Model $item): array;

    public function values(): array;
}
