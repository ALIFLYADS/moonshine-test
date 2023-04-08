<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Fields;

use Leeto\MoonShine\Traits\Fields\WithExt;
use Leeto\MoonShine\Traits\Fields\WithMask;

class Text extends Field
{
    use WithMask;
    use WithExt;

    protected static string $view = 'moonshine::fields.input';

    protected string $type = 'text';
}
