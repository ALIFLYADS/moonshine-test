<?php

declare(strict_types=1);

namespace MoonShine\Collections;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use MoonShine\Contracts\Fields\HasFields;
use MoonShine\Decorations\Decoration;
use MoonShine\Decorations\Tabs;
use MoonShine\Fields\Field;
use Throwable;

/**
 * @template T
 * @template TKey of array-key
 *
 * @extends Collection<TKey, T>
 */
abstract class MoonShineRenderElements extends Collection
{
    use Conditionable;

    /**
     * @throws Throwable
     */
    protected function extractOnly($elements, string $type, array &$data): void
    {
        foreach ($elements as $element) {
            if ($element instanceof Tabs) {
                foreach ($element->tabs() as $tab) {
                    $this->extractOnly($tab->getFields(), $type, $data);
                }
            } elseif ($element instanceof Decoration) {
                $this->extractOnly($element->getFields(), $type, $data);
            } elseif ($element instanceof $type) {
                $data[] = $element;
            }
        }
    }

    /**
     * @throws Throwable
     */
    protected function extractFields($elements, array &$data): void
    {
        foreach ($elements as $element) {
            if ($element instanceof Tabs) {
                foreach ($element->tabs() as $tab) {
                    $this->extractFields($tab->getFields(), $data);
                }
            } elseif ($element instanceof Field && $element instanceof HasFields) {
                $data[] = $element;
            } elseif ($element instanceof HasFields) {
                $this->extractFields($element->getFields(), $data);
            } elseif ($element instanceof Field) {
                $data[] = $element;
            }
        }
    }

    public function exceptElements(Closure $except): self
    {
        return $this->filter(function ($element) use ($except) {
            if ($except($element) === true) {
                return false;
            }

            if ($element instanceof Tabs) {
                foreach ($element->tabs() as $tab) {
                    $tab->fields(
                        $tab->getFields()->exceptElements($except)->toArray()
                    );
                }

                return true;
            }

            if ($element instanceof HasFields) {
                $element->fields(
                    $element->getFields()->exceptElements($except)->toArray()
                );

                return true;
            }

            return true;
        })->filter()->values();
    }
}
