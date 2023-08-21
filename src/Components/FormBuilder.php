<?php

declare(strict_types=1);

namespace MoonShine\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\ComponentAttributeBag;
use MoonShine\Fields\Fields;
use MoonShine\Fields\FormElement;
use Throwable;

/**
 * @method static static make(string $action = '', string $method = 'POST', Fields|array $fields = [], array $values = [])
 */
final class FormBuilder extends RowComponent
{
    protected $except = [
        'fields',
        'buttons',
        'submitLabel',
    ];

    protected bool $isPrecognitive = false;

    protected bool $isAsync = false;

    protected ?string $submitLabel = null;

    protected ComponentAttributeBag $submitAttributes;

    protected string $formName = 'default';

    public function __construct(
        protected string $action = '',
        protected string $method = 'POST',
        Fields|array $fields = [],
        protected array $values = []
    ) {
        $this->fields($fields);

        $this->submitAttributes = $this->newAttributeBag([
            'type' => 'submit',
        ]);

        $this->withAttributes([
            'action' => $this->action,
            'method' => $this->method,
            'enctype' => 'multipart/form-data',
            'x-data' => 'formBuilder',
        ]);
    }

    public function customAttributes(array $attributes): static
    {
        $this->attributes = $this->attributes->merge($attributes);

        return $this;
    }

    public function action(string $action): self
    {
        $this->action = $action;

        $this->customAttributes(['action' => $this->action]);

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function precognitive(): self
    {
        $this->isPrecognitive = true;

        return $this;
    }

    public function isPrecognitive(): bool
    {
        return $this->isPrecognitive;
    }

    public function async(): self
    {
        $this->isAsync = true;

        return $this;
    }

    public function isAsync(): bool
    {
        return $this->isAsync;
    }


    public function method(string $method): self
    {
        $this->method = $method;

        $this->customAttributes(['method' => $this->method]);

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function formName(string $formName): self
    {
        $this->formName = $formName;

        $this->getFields()->onlyFields()->each(fn(FormElement $field) => $field->form($formName));

        return $this;
    }

    public function submit(string $label, array $attributes = []): self
    {
        $this->submitLabel = $label;
        $this->submitAttributes->setAttributes(
            $attributes + $this->submitAttributes->getAttributes()
        );

        return $this;
    }

    public function submitLabel(): string
    {
        return $this->submitLabel ?? __('moonshine::ui.save');
    }

    /**
     * @throws Throwable
     */
    public function render(): View|Closure|string
    {
        $fields = $this->preparedFields();

        $xInit = json_encode([
            'whenFields' => array_values($fields->whenFieldsConditions()->toArray()),
        ], JSON_THROW_ON_ERROR);

        $this->customAttributes([
            'x-on:submit.prevent' => $this->isPrecognitive()
                ? 'precognition($event.target)'
                : '$event.target.submit()',
            'x-init' => "init($xInit)",
        ]);

        return view('moonshine::components.form.builder', [
            'attributes' => $this->attributes ?: $this->newAttributeBag(),
            'formName' => $this->formName,
            'fields' => $fields,
            'buttons' => $this->getButtons(),
            'submitLabel' => $this->submitLabel(),
            'submitAttributes' => $this->submitAttributes,
        ]);
    }
}
