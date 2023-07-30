<?php

declare(strict_types=1);

namespace MoonShine\Resources;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use MoonShine\Exceptions\ResourceException;
use MoonShine\Fields\Field;
use MoonShine\Fields\Fields;
use MoonShine\Traits\Resource\ResourceModelCrudRouter;
use MoonShine\Traits\Resource\ResourceModelPolicy;
use MoonShine\Traits\Resource\ResourceModelQuery;

abstract class ModelResource extends Resource
{
    use ResourceModelPolicy;
    use ResourceModelQuery;
    use ResourceModelCrudRouter;

    public string $model;

    protected string $routeAfterSave = 'index';

    abstract public function fields(): array;

    public function getFields(): Fields
    {
        return Fields::make($this->fields());
    }

    public function getActiveActions()
    {
        return ['edit'];
    }

    public function getModel(): Model
    {
        return new $this->model();
    }

    /**
     * Get an array of validation rules for resource related model
     *
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    abstract public function rules(Model $item): array;

    /**
     * Get custom messages for validator errors
     *
     * @return array<string, string|array<string, string>>
     */
    public function validationMessages(): array
    {
        return [];
    }

    /**
     * @throws Throwable
     */
    public function validate(Model $item): ValidatorContract
    {
        return Validator::make(
            moonshineRequest()->all(),
            $this->rules($item),
            array_merge(
                trans('moonshine::validation'),
                $this->validationMessages()
            ),
            $this->getFields()->extractLabels()
        );
    }

    public function prepareForValidation(): void
    {
    }

    public function getItem(): ?Model
    {
        return $this->getModel()
            ->newQuery()
            ->find(request('item'));
    }

    public function getItemOrInstance(): Model
    {
        return $this->getModel()
            ->newQuery()
            ->findOrNew(request('item'));
    }

    public function getItemOrFail(): Model
    {
        return $this->getModel()
            ->newQuery()
            ->findOrFail(request('item'));
    }

    /**
     * @throws ResourceException|Throwable
     */
    public function save(
        Model $item,
        ?Collection $fields = null,
        ?array $saveData = null
    ): Model {
        $fields ??= $this->getFields()->formFields()->fillValues($item->toArray(), $item);

        try {
            $fields->each(fn (Field $field) => $field->beforeSave());

            if (! $item->exists && method_exists($this, 'beforeCreating')) {
                $this->beforeCreating($item);
            }

            if ($item->exists && method_exists($this, 'beforeUpdating')) {
                $this->beforeUpdating($item);
            }

            foreach ($fields as $field) {
                //if (! $field->hasRelationship() || $field->belongToOne()) {
                $item = $this->saveItem($item, $field, $saveData);
                //}
            }

            if ($item->save()) {
                $wasRecentlyCreated = $item->wasRecentlyCreated;

                foreach ($fields as $field) {
                    //if ($field->hasRelationship() && ! $field->belongToOne()) {
                    $item = $this->saveItem($item, $field, $saveData);
                    //}
                }

                $item->save();

                $fields->each(fn (Field $field) => $field->afterSave());

                if ($wasRecentlyCreated && method_exists(
                    $this,
                    'afterCreated'
                )) {
                    $this->afterCreated($item);
                }

                if (! $wasRecentlyCreated && method_exists(
                    $this,
                    'afterUpdated'
                )) {
                    $this->afterUpdated($item);
                }

                //$this->setItem($item);
            }
        } catch (QueryException $queryException) {
            throw new ResourceException($queryException->getMessage());
        }

        return $item;
    }

    protected function saveItem(
        Model $item,
        Field $field,
        ?array $saveData = null
    ): Model {
        if (! $field->isCanSave()) {
            return $item;
        }

        if (is_null($saveData)) {
            $item->{$field->column()} = $field->requestValue();

            return $item;
        }

        if (isset($saveData[$field->column()])) {
            $item->{$field->column()} = $saveData[$field->column()];
        }

        return $item;
    }
}
