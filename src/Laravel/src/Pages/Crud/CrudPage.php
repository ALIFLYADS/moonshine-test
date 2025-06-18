<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Pages\Crud;

use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Modal;

/**
 * @template  TResource of CrudResourceContract = \MoonShine\Laravel\Resources\CrudResource
 * @extends Page<TResource>
 */
abstract class CrudPage extends Page implements CrudPageContract
{
    protected bool $isAsync = true;

    public function isAsync(): bool
    {
        if ($this->isAsync === false) {
            return false;
        }

        return $this->getResource()->isAsync();
    }

    /**
     * @return list<ComponentContract>
     */
    protected function fields(): iterable
    {
        return [];
    }

    protected function prepareFields(FieldsContract $fields): FieldsContract
    {
        return $fields;
    }

    public function getFields(): FieldsContract
    {
        return $this->prepareFields(
            $this->getCore()->getFieldsCollection($this->fields())
        );
    }

    /**
     * @return list<Modal>
     */
    public function getEmptyModals(): array
    {
        $components = [];

        if ($this->getResource()->isEditInModal()) {
            $components[] = Modal::make(
                __('moonshine::ui.edit'),
                components: [
                    Div::make()->customAttributes(['id' => 'resource-edit-modal']),
                ],
            )
                ->wide()
                ->name('resource-edit-modal');
        }

        if ($this->getResource()->isDetailInModal()) {
            $components[] = Modal::make(
                __('moonshine::ui.show'),
                components: [
                    Div::make()->customAttributes(['id' => 'resource-detail-modal']),
                ],
            )
                ->wide()
                ->name('resource-detail-modal');
        }

        return $components;
    }
}
