<?php

namespace Leeto\MoonShine\Filters;

use Leeto\MoonShine\Traits\Fields\SearchableTrait;

class SelectFilter extends Filter
{
    use SearchableTrait;

    public static string $view = 'select';
}