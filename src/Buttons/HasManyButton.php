<?php

declare(strict_types=1);

namespace MoonShine\Buttons;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use MoonShine\ActionButtons\ActionButton;
use MoonShine\Components\FormBuilder;
use MoonShine\Enums\PageType;
use MoonShine\Fields\Field;
use MoonShine\Fields\Fields;
use MoonShine\Fields\Hidden;
use MoonShine\Fields\Relationships\HasMany;
use MoonShine\Fields\StackFields;
use Throwable;

final class HasManyButton
{
    /**
     * @throws Throwable
     */
    public static function for(HasMany $field, bool $update = false): ActionButton
    {
        $resource = $field->getResource();
        $parent = $field->getRelatedModel();

        if (! $resource->formPage()) {
            return ActionButton::emptyHidden();
        }

        $action = $update
            ? static fn (Model $data) => $resource->route('crud.update', $data->getKey())
            : static fn (?Model $data) => $resource->route('crud.store');

        $isAsync = $resource->isAsync() || $field->isAsync();

        $getFields = function () use ($resource, $field, $isAsync, $parent, $update) {
            $fields = $resource->getFormFields();

            $fields->onlyFields()
                ->unwrapElements(StackFields::class)
                ->each(fn (Field $nestedFields): Field => $nestedFields->setParent($field))
                // Uncomment if you need a parent resource
                //->onlyRelationFields()
                //->each(fn (ModelRelationField $nestedFields): Field => $nestedFields->setParentResource($resource))
            ;

            $fields = $fields->withoutForeignField();

            return $fields->when(
                $field->getRelation() instanceof MorphOneOrMany,
                fn (Fields $f) => $f->push(
                    Hidden::make($field->getRelation()?->getQualifiedMorphType())
                        ->setValue($parent::class)
                )
            )->when(
                $update,
                fn (Fields $f) => $f->push(
                    Hidden::make('_method')->setValue('PUT'),
                )
            )
                ->push(
                    Hidden::make($field->getRelation()?->getForeignKeyName())
                        ->setValue($parent->getKey())
                )
                ->push(Hidden::make('_async_field')->setValue($isAsync))
                ->toArray();
        };

        $authorize = $update
            ? fn (?Model $item): bool => ! is_null($item) && in_array('update', $resource->getActiveActions())
            && $resource->setItem($item)->can('update')
            : fn (?Model $item): bool => in_array('create', $resource->getActiveActions())
            && $resource->can('create');

        return ActionButton::make($update ? '' : __('moonshine::ui.add'), url: $action)
            ->canSee($authorize)
            ->inModal(
                title: fn (): array|string|null => __($update ? 'moonshine::ui.create' : 'moonshine::ui.edit'),
                content: fn (?Model $data): string => (string) FormBuilder::make($action($data))
                    ->switchFormMode(
                        $isAsync,
                        'table-updated-' . $field->getRelationName()
                    )
                    ->name($field->getRelationName())
                    ->when(
                        $update,
                        fn (FormBuilder $form): FormBuilder => $form->fillCast(
                            $data,
                            $resource->getModelCast()
                        ),
                        fn (FormBuilder $form): FormBuilder => $form->fillCast(
                            [$field->getRelation()?->getForeignKeyName() => $parent?->getKey()],
                            $resource->getModelCast()
                        )
                    )
                    ->submit(__('moonshine::ui.save'), ['class' => 'btn-primary btn-lg'])
                    ->fields($getFields)
                    ->redirect(
                        $isAsync ? null : to_page(
                            moonshineRequest()->getResource()
                                ?->getPages()
                                ?->findByType(PageType::FORM),
                            moonshineRequest()->getResource(),
                            params: ['resourceItem' => $parent->getKey()]
                        )
                    ),
                wide: true,
                closeOutside: false,
            )
            ->primary()
            ->icon($update ? 'heroicons.outline.pencil' : 'heroicons.outline.plus');
    }
}
