<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts;

use Closure;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;

interface HasMetricsContract
{
    /**
     * @return list<Metric>
     */
    public function getMetrics(): array;

    /**
     * @return null|Closure(array $components): Fragment
     */
    public function getFragmentMetrics(): ?Closure;
}
