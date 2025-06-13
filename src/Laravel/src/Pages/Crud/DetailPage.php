<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Pages\Crud;

use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\Collection\ActionButtonsContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Core\Exceptions\PageException;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Laravel\Contracts\Fields\HasTabModeContract;
use MoonShine\Laravel\Contracts\Page\DetailPageContract;
use MoonShine\Laravel\Fields\Relationships\ModelRelationField;
use MoonShine\Laravel\Resources\CrudResource;
use MoonShine\Support\Enums\Ability;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\PageType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Collections\ActionButtons;
use MoonShine\UI\Components\ActionGroup;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Contracts\FieldsWrapperContract;
use MoonShine\UI\Exceptions\MoonShineComponentException;
use Throwable;

/**
 * @template TResource of CrudResource = \MoonShine\Laravel\Resources\ModelResource
 * @extends CrudPage<TResource>
 */
class DetailPage extends CrudPage implements DetailPageContract
{
    protected ?PageType $pageType = PageType::DETAIL;

    public function getTitle(): string
    {
        return $this->title ?: __('moonshine::ui.show');
    }

    protected function prepareFields(FieldsContract $fields): FieldsContract
    {
        /** @var Fields $fields */
        return $fields->ensure([FieldsWrapperContract::class, FieldContract::class, ModelRelationField::class]);
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        if (! \is_null($this->breadcrumbs)) {
            return $this->breadcrumbs;
        }

        $breadcrumbs = parent::getBreadcrumbs();

        $breadcrumbs[$this->getRoute()] = data_get($this->getResource()->getItem(), $this->getResource()->getColumn());

        return $breadcrumbs;
    }

    /**
     * @throws ResourceException
     */
    protected function prepareBeforeRender(): void
    {
        abort_if(
            ! $this->getResource()->hasAction(Action::VIEW)
            || ! $this->getResource()->can(Ability::VIEW),
            403
        );

        parent::prepareBeforeRender();
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function components(): iterable
    {
        $this->validateResource();

        if (! $this->getResource()->isItemExists()) {
            oops404();
        }

        return $this->getLayers();
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        $resource = $this->getResource();
        $item = $resource->getCastedData();

        return [
            Box::make([
                ...$this->getDetailComponents($item),
                LineBreak::make(),
                ...$this->getTopButtons(),
            ]),
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        $components = [];
        $item = $this->getResource()->getItem();

        if (! $this->getResource()->isItemExists()) {
            return $components;
        }

        $outsideFields = $this->getResource()->getDetailFields(onlyOutside: true);

        $tabs = [];

        if ($outsideFields->isNotEmpty()) {
            $components[] = LineBreak::make();

            /** @var ModelRelationField $field */
            foreach ($outsideFields as $field) {
                $field->fillCast(
                    $item,
                    $field->getResource()?->getCaster()
                );

                if ($field->isToOne()) {
                    $field
                        ->withoutWrapper()
                        ->previewMode();
                }

                if ($field instanceof HasTabModeContract && $field->isTabMode()) {
                    $tabs[] = Tab::make($field->getLabel(), [
                        $field->isToOne() ? Box::make($field->getLabel(), [$field]) : $field,
                    ]);

                    continue;
                }

                $components[] = LineBreak::make();

                $blocks = $field->isToOne()
                    ? [Box::make($field->getLabel(), [$field])]
                    : [Heading::make($field->getLabel()), $field];

                $components[] = Fragment::make($blocks)
                    ->name($field->getRelationName());
            }
        }

        if ($tabs !== []) {
            $components[] = Tabs::make($tabs);
        }

        return array_merge($components, $this->getEmptyModals());
    }

    protected function getDetailComponent(?DataWrapperContract $item, Fields $fields): ComponentContract
    {
        return $this->modifyDetailComponent(
            TableBuilder::make($fields)
                ->cast($this->getResource()->getCaster())
                ->items([$item])
                ->vertical(
                    title: $this->getResource()->isDetailInModal() ? 3 : 2,
                    value: $this->getResource()->isDetailInModal() ? 9 : 10,
                )
                ->simple()
                ->preview()
                ->class('table-divider')
        );
    }

    protected function modifyDetailComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     * @throws MoonShineComponentException
     * @throws PageException
     * @throws Throwable
     */
    protected function getDetailComponents(?DataWrapperContract $item): array
    {
        return [
            Fragment::make([
                $this->getDetailComponent($item, $this->getResource()->getDetailFields()),
            ])->name('crud-detail'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, [
            $this->getResource()->getEditButton(
                isAsync: $this->isAsync(),
            ),
            $this->getResource()->getDeleteButton(
                redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(),
                isAsync: false
            ),
        ]);
    }

    public function getButtons(): ActionButtonsContract
    {
        return ActionButtons::make(
            $this->buttons()->toArray()
        )->withoutBulk();
    }

    protected function getTopButtons(): array
    {
        return [
            ActionGroup::make(
                $this->getButtons()
            )
                ->fill($this->getResource()->getCastedData())
                ->class('justify-end'),
        ];
    }
}
