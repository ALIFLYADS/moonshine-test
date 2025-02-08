<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Traits\Fields;

use Closure;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Buttons\BelongsToOrManyButton;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use Throwable;

trait BelongsToOrManyCreatable
{
    protected bool $isCreatable = false;

    protected ?ActionButtonContract $creatableButton = null;

    protected ?string $creatableFragmentUrl = null;

    public function creatable(
        Closure|bool|null $condition = null,
        ?ActionButtonContract $button = null,
        ?string $fragmentUrl = null
    ): static {
        $this->isCreatable = value($condition, $this) ?? true;
        $this->creatableButton = $button;
        $this->creatableFragmentUrl = $fragmentUrl;

        return $this;
    }

    public function isCreatable(): bool
    {
        return $this->isCreatable;
    }

    /**
     * @throws Throwable
     */
    public function getCreateButton(): ?ActionButtonContract
    {
        if (! $this->isCreatable()) {
            return null;
        }

        if ($this->getParent() instanceof BelongsToMany) {
            return null;
        }

        $button = BelongsToOrManyButton::for($this, button: $this->creatableButton);

        return $button->isSee()
            ? $button
            : null;
    }

    public function getFragmentUrl(): string
    {
        $resource = $this->getNowOnResource() ?? moonshineRequest()->getResource();
        $page = $this->getNowOnPage() ?? moonshineRequest()->getPage();
        $itemID = data_get($this->getNowOnQueryParams(), 'resourceItem', moonshineRequest()->getItemID());

        return $this->creatableFragmentUrl ?? toPage(
            page: $page,
            resource: $resource,
            params: ['resourceItem' => $itemID],
            fragment: $this->getRelationName()
        );
    }
}
