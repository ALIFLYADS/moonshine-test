<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Concerns\Page;

use Closure;
use Illuminate\Support\Collection;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Laravel\Contracts\Page\IndexPageContract;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;

/**
 * @mixin IndexPageContract
 */
trait HasMetrics
{
    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    public function getMetrics(): array
    {
        return Collection::make($this->metrics())
            ->ensure(Metric::class)
            ->toArray();
    }

    /**
     * @return null|Closure(array $components): Fragment
     */
    protected function fragmentMetrics(): ?Closure
    {
        return null;
    }

    /**
     * @return null|Closure(array $components): Fragment
     */
    public function getFragmentMetrics(): ?Closure
    {
        return $this->fragmentMetrics();
    }
}
