<?php

namespace Leeto\MoonShine\Fields;

use Leeto\MoonShine\Traits\Fields\WithMask;

class Email extends Field
{
    use WithMask;

    protected static string $view = 'input';

    protected static string $type = 'email';
}
