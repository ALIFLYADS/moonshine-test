<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Traits\Conditionable;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\Traits\Makeable;

/** @method static static make(array $data = []) */
final class MoonShineJsonResponse extends JsonResponse
{
    use Makeable;
    use Conditionable;

    protected array $jsonData = [];

    public function __construct(array $data = [])
    {
        parent::__construct();

        $this->mergeJsonData($data);
    }

    public function mergeJsonData(array $data): self
    {
        $this->jsonData = array_filter(
            array_merge($this->jsonData, $data)
        );

        return $this->setData($this->jsonData);
    }

    public function toast(string $value, ToastType $type = ToastType::DEFAULT): self
    {
        return $this->mergeJsonData([
            'message' => $value,
            'messageType' => $type->value,
        ]);
    }

    public function redirect(string $value): self
    {
        return $this->mergeJsonData(['redirect' => $value]);
    }

    public function events(array $events): self
    {
        return $this->mergeJsonData(['events' => AlpineJs::prepareEvents($events)]);
    }

    /**
     * @param  string|array<string, string>  $value
     */
    public function html(string|array $value): self
    {
        return $this->mergeJsonData(['html' => $value]);
    }

    /**
     * @see html()
     */
    public function innerHtml(string|array $value): self
    {
        return $this->html($value);
    }

    /**
     * @param  string|array<string, string>  $value
     */
    public function outerHtml(string|array $value): self
    {
        return $this->mergeJsonData(['outer_html' => $value]);
    }

    /**
     * @param  array<string, string>  $value
     */
    public function prepend(array $value): self
    {
        return $this->mergeJsonData(['prepend' => $value]);
    }

    /**
     * @param  array<string, string>  $value
     */
    public function append(array $value): self
    {
        return $this->mergeJsonData(['append' => $value]);
    }

    /**
     * @param  array<string, string>  $value
     */
    public function before(array $value): self
    {
        return $this->mergeJsonData(['before' => $value]);
    }

    public function after(array $value): self
    {
        return $this->mergeJsonData(['after' => $value]);
    }

    /**
     * @param  array<string, string>  $value
     */
    public function fieldsValues(array $value): self
    {
        return $this->mergeJsonData(['fields_values' => $value]);
    }
}
