<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Forms;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Contracts\UI\FormContract;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Fields\Relationships\ModelRelationField;
use MoonShine\Laravel\Resources\CrudResource;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\Traits\Makeable;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Fields\Hidden;
use RuntimeException;
use Stringable;
use Throwable;

/**
 * @method static static make(CrudResourceContract $resource)
 */
final readonly class FiltersForm implements FormContract
{
    use Makeable;

    public function __construct(private CrudResourceContract $resource)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(): FormBuilderContract
    {
        /** @var CrudResource $resource */
        $resource = $this->resource;
        $page = $resource->getIndexPage();

        if ($page === null) {
            throw new RuntimeException('Index page not defined');
        }

        $resource->setQueryParams(
            request()->only($resource->getQueryParamsKeys()),
        );

        $values = $resource->getFilterParams();
        $filters = $resource->getFilters();

        $action = $page->isAsync() ? '#' : $this->getFormAction();

        foreach ($filters->onlyFields() as $filter) {
            if (! $filter instanceof ModelRelationField) {
                $filter->fillData($values);
                data_forget($values, $filter->getColumn());
            }
        }

        $sort = request()->getScalar('sort');
        $queryTag = request()->getScalar('query-tag');

        return FormBuilder::make($action, FormMethod::GET)
            ->name('filters')
            ->fillCast($values, $resource->getCaster())
            ->fields(
                $filters
                    ->when(
                        $sort,
                        static fn ($fields): Fields
                            => $fields
                            ->prepend(
                                Hidden::make(column: 'sort')->setValue(
                                    $sort,
                                ),
                            ),
                    )
                    ->when(
                        $queryTag,
                        static fn ($fields): Fields
                            => $fields
                            ->prepend(
                                Hidden::make(column: 'query-tag')->setValue(
                                    $queryTag,
                                ),
                            ),
                    )
                    ->toArray(),
            )
            ->when($page->isAsync(), function (FormBuilderContract $form) use ($resource, $page): void {
                $events = [
                    $resource->getListEventName(),
                    AlpineJs::event(JsEvent::OFF_CANVAS_TOGGLED, 'filters-off-canvas'),
                ];

                $form->customAttributes([
                    '@submit.prevent' => "asyncFilters(
                        `" . AlpineJs::prepareEvents($events) . "`,
                        `_component_name,_token,_method`
                    )",
                ]);

                $form->buttons([
                    $this->getResetButton($page->isAsync(), true),
                ]);
            })
            ->submit(__('moonshine::ui.search'), ['class' => 'btn-primary'])
            ->when(
                $resource->getFilterParams() !== [],
                fn (FormBuilderContract $form): FormBuilderContract => $form->buttons([
                    $this->getResetButton(),
                ]),
            );
    }

    private function getResetButton(bool $async = false, bool $hide = false): ActionButton
    {
        $button = ActionButton::make(
            __('moonshine::ui.reset'),
            $this->getFormAction(query: ['reset' => true]),
        )
            ->secondary()
            ->showInLine()
            ->class('js-async-reset-button');

        if ($hide) {
            $button = $button->customAttributes([
                'style' => 'display: none',
            ]);
        }

        if (! $async) {
            return $button;
        }

        return $button
            ->dispatchEvent([
                AlpineJs::event(
                    JsEvent::FORM_RESET,
                    'filters',
                ),
                AlpineJs::event(
                    JsEvent::FORM_SUBMIT,
                    'filters',
                ),
            ], withoutPayload: true);
    }

    private function getFormAction(array $query = []): string
    {
        return Str::of(request()->url())->when(
            $query,
            static fn (Stringable $str): Stringable
                => $str
                ->append('?')
                ->append(Arr::query($query)),
        )->value();
    }
}
