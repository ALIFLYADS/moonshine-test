<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Traits\Conditionable;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\Traits\Makeable;
use MoonShine\UI\Enums\HtmlMode;

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

    protected function mergeJsonData(array $data): self
    {
        $this->jsonData = array_filter(
            array_merge($this->jsonData, $data)
        );

        return $this->setData($this->jsonData);
    }

    public function toast(string $value, ToastType $type = ToastType::DEFAULT, null|int|false $duration = null): self
    {
        return $this->mergeJsonData([
            'message' => $value,
            'messageType' => $type->value,
            'messageDuration' => $duration === false ? -1 : $duration,
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

    public function html(string|array $value, HtmlMode $mode = HtmlMode::INNER_HTML): self
    {
        return $this->mergeJsonData(['html' => $value, 'htmlMode' => $mode->value]);
    }

    public function htmlData(string|array $value, string $selector, HtmlMode $mode = HtmlMode::INNER_HTML): self
    {
        if (! isset($this->jsonData['htmlData'])) {
            $this->jsonData['htmlData'] = [];
        }

        $this->jsonData['htmlData'][] = [
            'html' => $value,
            'selector' => $selector,
            'htmlMode' => $mode->value,
        ];

        return $this->setData($this->jsonData);
    }

    /**
     * @param  array<string, string>  $value
     */
    public function fieldsValues(array $value): self
    {
        return $this->mergeJsonData(['fields_values' => $value]);
    }
}
