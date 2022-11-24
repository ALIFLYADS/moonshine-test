<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Fields;

use Illuminate\Database\Eloquent\Model;

use Leeto\MoonShine\Contracts\Fields\Fileable;
use Leeto\MoonShine\Contracts\Fields\HasFields;
use Leeto\MoonShine\Contracts\Fields\Relationships\HasRelationship;
use Leeto\MoonShine\Contracts\Fields\Relationships\OneToManyRelation;
use Leeto\MoonShine\Traits\Fields\WithFields;
use Leeto\MoonShine\Traits\Fields\WithRelationship;

class HasMany extends Field implements HasRelationship, HasFields, OneToManyRelation
{
    use WithFields;
    use WithRelationship;

    protected static string $view = 'moonshine::fields.has-many';

    protected bool $group = true;

    public function indexViewValue(Model $item, bool $container = false): mixed
    {
        $columns = [];
        $values = [];

        foreach ($this->getFields() as $field) {
            $columns[$field->field()] = $field->label();
        }

        foreach ($item->{$this->field()} as $index => $item) {
            foreach ($this->getFields() as $field) {
                $values[$index][$field->field()] = $field->indexViewValue($item, false);
            }
        }

        return view('moonshine::shared.table', [
            'columns' => $columns,
            'values' => $values
        ]);
    }

    public function exportViewValue(Model $item): mixed
    {
        return '';
    }

    public function save(Model $item): Model
    {
        $related = $this->getRelated($item);
        $primaryKey = $related->getKeyName();

        $currentIdentities = [];
        $prevIdentities = $item->{$this->relation()}
            ->pluck($primaryKey)
            ->toArray();

        if ($this->requestValue() !== false) {
            foreach ($this->requestValue() as $values) {
                $identity = null;

                foreach ($this->getFields() as $field) {
                    if ($field instanceof ID) {
                        $identity = $values[$field->field()] ?? null;
                        $currentIdentities[$identity] = $identity;
                    }

                    if ($field instanceof Fileable && isset($values[$field->field()])) {
                        $values[$field->field()] = $field->store($values[$field->field()]);
                    }
                }

                $item->{$this->relation()}()->updateOrCreate([
                    $primaryKey => $identity
                ], $values);
            }
        }

        $item->{$this->relation()}()
            ->whereIn($primaryKey, collect($prevIdentities)->diff($currentIdentities)->toArray())
            ->delete();

        return $item;
    }
}
