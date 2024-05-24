<?php

declare(strict_types=1);

namespace MoonShine\Handlers;

use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MoonShine\Components\ActionButtons\ActionButton;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Exceptions\ActionException;
use MoonShine\Jobs\ExportHandlerJob;
use MoonShine\MoonShineUI;
use MoonShine\Notifications\MoonShineNotification;
use MoonShine\Traits\WithStorage;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExportHandler extends Handler
{
    use WithStorage;

    protected ?string $icon = 'table-cells';

    protected bool $isCsv = false;

    protected string $csvDelimiter = ',';

    public function csv(): static
    {
        $this->isCsv = true;

        return $this;
    }

    public function delimiter(string $value): static
    {
        $this->csvDelimiter = $value;

        return $this;
    }

    /**
     * @throws ActionException
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException|Throwable
     */
    public function handle(): Response
    {
        $query = collect(
            request()->query()
        )->except(['_component_name', 'page'])->toArray();

        if (! $this->hasResource()) {
            throw ActionException::resourceRequired();
        }

        $this->resolveStorage();

        $path = Storage::disk($this->getDisk())
            ->path(
                "{$this->getDir()}/{$this->getResource()->uriKey()}." . ($this->isCsv() ? 'csv' : 'xlsx')
            );

        if ($this->isQueue()) {
            ExportHandlerJob::dispatch(
                $this->getResource()::class,
                $path,
                $query,
                $this->getDisk(),
                $this->getDir(),
                $this->getDelimiter()
            );

            MoonShineUI::toast(
                __('moonshine::ui.resource.queued')
            );

            return back();
        }

        return response()->download(
            self::process(
                $path,
                $this->getResource(),
                $query,
                $this->getDisk(),
                $this->getDir(),
                $this->getDelimiter()
            )
        );
    }

    public function isCsv(): bool
    {
        return $this->isCsv;
    }

    public function getDelimiter(): string
    {
        return $this->csvDelimiter;
    }

    /**
     * @throws WriterNotOpenedException
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException|Throwable
     */
    public static function process(
        string $path,
        ResourceContract $resource,
        array $query,
        string $disk = 'public',
        string $dir = '/',
        string $delimiter = ','
    ): string {
        // TODO fix it in 3.0
        if(app()->runningInConsole()) {
            request()->merge($query);
        }

        $items = static function (ResourceContract $resource): Generator {
            foreach ($resource->resolveQuery()->cursor() as $index => $item) {
                $row = [];

                $fields = $resource->getExportFields();

                $fields->fill($item->toArray(), $item, $index);

                foreach ($fields as $field) {
                    $row[$field->getLabel()] = $field
                        ->rawMode()
                        ->preview();
                }

                yield $row;
            }
        };

        $fastExcel = new FastExcel($items($resource));

        if (str($path)->contains('.csv')) {
            $fastExcel->configureCsv($delimiter);
        }

        $result = $fastExcel->export($path);

        $url = str($path)
            ->remove(Storage::disk($disk)->path($dir))
            ->value();

        MoonShineNotification::send(
            trans('moonshine::ui.resource.export.exported'),
            [
                'link' => Storage::disk($disk)->url(trim($dir, '/') . $url),
                'label' => trans('moonshine::ui.download'),
            ]
        );

        return $result;
    }

    /**
     * @throws ActionException
     */
    public function getButton(): ActionButton
    {
        if (! $this->hasResource()) {
            throw ActionException::resourceRequired();
        }

        $query = Arr::query(request(['filters', 'sort', 'query-tag'], []));
        $url = $this->getResource()?->route('handler', query: ['handlerUri' => $this->uriKey()]);

        return ActionButton::make(
            $this->getLabel(),
            $url . ($query ? '?' . $query : '')
        )
            ->primary()
            ->customAttributes(['class' => '_change-query', 'data-original-url' => $url])
            ->icon($this->getIconValue(), $this->isCustomIcon(), $this->getIconPath());
    }
}
