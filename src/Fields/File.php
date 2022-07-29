<?php

namespace Leeto\MoonShine\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Leeto\MoonShine\Contracts\Fields\FileContract;
use Leeto\MoonShine\Traits\Fields\CanBeMultiple;
use Leeto\MoonShine\Traits\Fields\FileTrait;

class File extends Field implements FileContract
{
    use FileTrait, CanBeMultiple;

    protected static string $view = 'file';

    protected static string $type = 'file';

    protected string $accept = '*/*';

    protected array $attributes = [
        'accept'
    ];

    public function accept(string $value): static
    {
        $this->accept = $value;

        return $this;
    }

    public function indexViewValue(Model $item, bool $container = true): string
    {
        if($item->{$this->field()} == '') {
            return '';
        }

        if($this->isMultiple()) {
            return collect($item->{$this->field()})
                ->map(fn ($value, $index) => view('moonshine::fields.shared.file', [
                    'value' => Storage::url($value),
                    'index' => $index+1,
                    'canDownload' => $this->canDownload(),
                ])->render())->implode('');
        }

        return view(
            'moonshine::fields.shared.file', [
                'value' => parent::indexViewValue($item),
                'canDownload' => $this->canDownload(),
        ]);
    }
}
