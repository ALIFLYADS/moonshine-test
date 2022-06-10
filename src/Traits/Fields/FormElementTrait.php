<?php


namespace Leeto\MoonShine\Traits\Fields;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Database\Eloquent\Model;
use Leeto\MoonShine\Contracts\Fields\FieldHasRelationContract;
use Leeto\MoonShine\Contracts\Resources\ResourceContract;
use Closure;

trait FormElementTrait
{
    protected string $label = '';

    protected string $field;

    protected string|null $relation = null;

    protected ResourceContract|null $resource;

    protected string $resourceTitleField = '';

    protected Closure|null $resourceTitleCallback = null;

    protected static string $view = '';

    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    final public function __construct(string $label = null, string $field = null, Closure|ResourceContract|string|null $resource = null)
    {
        $this->setLabel($label ?? str($this->label)->ucfirst());
        $this->setField($field ?? str($this->label)->lower()->snake());

        if($this instanceof FieldHasRelationContract) {
            if(!$this->isRelationToOne() && !$this->isRelationHasOne()) {
                $this->multiple();
            }

            $this->setField($field ?? str($this->label)->camel());

            if(($this->isRelationToOne() && !$this->isRelationHasOne()) && !str($this->field())->contains('_id')) {
                $this->setField(
                    str($this->field())
                        ->append('_id')
                        ->snake()
                );
            }

            $this->setRelation($field ?? str($this->label)->camel());

            if(str($this->relation())->contains('_id')) {
                $this->setRelation(
                    str($this->relation())
                        ->remove('_id')
                        ->camel()
                );
            }

            if($resource instanceof ResourceContract) {
                $this->setResource($resource);
            } elseif(is_callable($resource)) {
                $this->setResourceTitleCallback($resource);
            } elseif(is_string($resource)) {
                $this->setResourceTitleField($resource);
            }
        }
    }

    public function label(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function field(): string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function relation(): string|null
    {
        return $this->relation;
    }

    public function setRelation(string $relation): static
    {
        $this->relation = $relation;

        return $this;
    }

    public function resource(): ResourceContract|null
    {
        return $this->resource ?? $this->findResource();
    }

    protected function findResource(): ResourceContract|null
    {
        $resourceClass = (string) str('App\MoonShine\Resources\\')
            ->append(str($this->relation() ?? $this->field())->studly()->singular())
            ->append('Resource');

        return class_exists($resourceClass) ? new $resourceClass() : null;
    }

    public function setResource(ResourceContract|null $resource): void
    {
        $this->resource = $resource;
    }

    public function resourceTitleField(): string
    {
        if($this->resourceTitleField) {
            return $this->resourceTitleField;
        }
        return $this->resource() && $this->resource()->titleField()
            ? $this->resource()->titleField()
            : 'id';
    }

    public function setResourceTitleField(string $resourceTitleField): static
    {
        $this->resourceTitleField = $resourceTitleField;

        return $this;
    }

    public function resourceTitleCallback(): Closure|null
    {
        return $this->resourceTitleCallback;
    }

    protected function setResourceTitleCallback(Closure $resourceTitleCallback): void
    {
        $this->resourceTitleCallback = $resourceTitleCallback;
    }

    public function getView(): string
    {
        return static::$view;
    }

    public function requestValue(): mixed
    {
        return request(
            $this->nameDot(),
            $this->getDefault() ?? old($this->nameDot(), false)
        );
    }

}