<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Resources\CrudResource;
use Throwable;

class MoonShineFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        if ($this->getResource() !== null) {
            $this->errorBag = $this->getResource()->getUriKey();
        }

        if ($this->getResource()->getFormPage() !== null) {
            $this->getResource()->getFormPage()->prepareForValidation();
        }

        $this->request = request()->getPayload();
    }

    public function messages(): array
    {
        if ($this->getResource() !== null && $this->getResource()->getFormPage() !== null) {
            $messages = __('moonshine::validation');

            return array_merge(
                \is_array($messages) ? $messages : [],
                $this->getResource()->getFormPage()->validationMessages()
            );
        }

        return parent::messages();
    }

    /**
     * @throws Throwable
     */
    public function attributes(): array
    {
        return $this->hasResource()
            ? $this->getResource()
                ?->getFormFields()
                ?->onlyFields()
                ?->extractLabels()
            : [];
    }

    public function getResource(): ?CrudResource
    {
        return moonshineRequest()->getResource();
    }

    public function hasResource(): bool
    {
        return ! \is_null($this->getResource());
    }

    /**
     * @throws Throwable
     */
    public function beforeResourceAuthorization(): void
    {
        throw_if(
            ! $this->hasResource(),
            ResourceException::notDeclared()
        );
    }

    public function getPage(): PageContract
    {
        return moonshineRequest()->getPage();
    }
}
