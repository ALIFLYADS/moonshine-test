<?php

declare(strict_types=1);

namespace MoonShine\Components;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use MoonShine\ActionButtons\ActionButtons;
use MoonShine\Contracts\Table\TableContract;
use MoonShine\Fields\Field;
use MoonShine\Fields\Fields;
use MoonShine\Table\TableRow;
use MoonShine\Traits\Table\TableStates;

/**
 * @method static static make(Fields|array $fields = [], iterable $items = [], ?LengthAwarePaginator $paginator = null)
 */
final class TableBuilder extends IterableComponent implements TableContract
{
    use TableStates;

    protected string $view = 'moonshine::components.table.builder';

    protected $except = [
        'rows',
        'fields',
        'hasPaginator',
        'paginator',
    ];

    protected bool $isAsync = false;

    protected ?string $asyncUrl = null;

    protected array $rows = [];

    protected ?Closure $trAttributes = null;

    protected ?Closure $tdAttributes = null;

    protected ?string $tableUrl = null;

    public function __construct(
        Fields|array $fields = [],
        protected iterable $items = [],
        protected ?LengthAwarePaginator $paginator = null
    ) {
        $this->fields($fields);

        if ($items instanceof LengthAwarePaginator) {
            $this->paginator($items);
            $this->items($items->items());
        }

        $this->withAttributes([]);
    }

    public function isAsync(): bool
    {
        return $this->isAsync;
    }

    public function async(string $asyncUrl): self
    {
        $this->isAsync = true;

        $this->asyncUrl = $asyncUrl;

        return $this;
    }

    public function getAsyncUrl(): string
    {
        return $this->asyncUrl ?? '';
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

    public function paginator(LengthAwarePaginator $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    public function getPaginator(): ?LengthAwarePaginator
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


    public function tableUrl(string $tableUrl): self
    {
        $this->tableUrl = $tableUrl;
        return $this;
    }

    public function getTableUrl(): ?string
    {
        return $this->tableUrl;
    }

    protected function viewData(): array
    {
        return [
            'rows' => $this->rows(),
            'fields' => $this->getFields(),
            'name' => $this->getName(),
            'tableUrl' => $this->getTableUrl(),
            'hasPaginator' => $this->hasPaginator(),
            'paginator' => $this->getPaginator(),
            'bulkButtons' => $this->getBulkButtons(),
            'async' => $this->isAsync(),
            'asyncUrl' => $this->getAsyncUrl(),
        ] + $this->statesToArray();
    }
}
