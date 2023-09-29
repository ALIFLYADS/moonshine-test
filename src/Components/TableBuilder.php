<?php

declare(strict_types=1);

namespace MoonShine\Components;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MoonShine\ActionButtons\ActionButtons;
use MoonShine\Contracts\Table\TableContract;
use MoonShine\Fields\Field;
use MoonShine\Fields\Fields;
use MoonShine\Table\TableRow;
use MoonShine\Traits\HasAsync;
use MoonShine\Traits\Table\TableStates;

/**
 * @method static static make(Fields|array $fields = [], iterable $items = [], ?LengthAwarePaginator $paginator = null)
 */
final class TableBuilder extends IterableComponent implements TableContract
{
    use TableStates;
    use HasAsync;

    protected string $view = 'moonshine::components.table.builder';

    protected $except = [
        'rows',
        'fields',
        'hasPaginator',
        'paginator',
    ];

    protected array $rows = [];

    protected ?Closure $trAttributes = null;

    protected ?Closure $tdAttributes = null;

    public function __construct(
        Fields|array $fields = [],
        protected iterable $items = [],
        protected ?Paginator $paginator = null
    ) {
        $this->fields($fields);

        if ($items instanceof Paginator) {
            $this->paginator($items);
            $this->items($items->items());
        }

        $this->withAttributes([]);
    }

    public function getItems(): Collection
    {
        return collect($this->items)
            ->map(
                fn ($item): array => $this->hasCast()
                    ? $this->getCast()->dehydrate($item)
                    : (array) $item
            );
    }

    public function rows(): Collection
    {
        return $this->getItems()->map(function (array $data, $index): TableRow {
            $casted = $this->castData($data);

            $fields = $this->getFields();

            if (! is_null($this->getName())) {
                $fields->onlyFields()->each(
                    fn (Field $field): Field => $field->formName($this->getName())
                );
            }

            return TableRow::make(
                $casted,
                $fields->fillCloned($data, $casted, $index),
                $this->getButtons($data),
                $this->trAttributes,
                $this->tdAttributes
            );
        });
    }

    public function paginator(Paginator $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    public function getPaginator(): ?Paginator
    {
        return $this->paginator;
    }

    public function hasPaginator(): bool
    {
        return ! is_null($this->paginator);
    }

    public function getBulkButtons(): ActionButtons
    {
        return ActionButtons::make($this->buttons)
            ->bulk();
    }

    public function trAttributes(Closure $closure): self
    {
        $this->trAttributes = $closure;

        return $this;
    }

    public function tdAttributes(Closure $closure): self
    {
        $this->tdAttributes = $closure;

        return $this;
    }

    protected function viewData(): array
    {
        return [
            'rows' => $this->rows(),
            'fields' => $this->getFields(),
            'name' => $this->getName(),
            'hasPaginator' => $this->hasPaginator(),
            'simple' => ! $this->getPaginator() instanceof LengthAwarePaginator,
            'paginator' => $this->getPaginator(),
            'bulkButtons' => $this->getBulkButtons(),
            'async' => $this->isAsync(),
            'asyncUrl' => $this->asyncUrl(),
        ] + $this->statesToArray();
    }
}
