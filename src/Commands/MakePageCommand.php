<?php

declare(strict_types=1);

namespace MoonShine\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\outro;

use function Laravel\Prompts\text;

use MoonShine\MoonShine;
use Illuminate\Support\Collection;

class MakePageCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:page {className?} {--dir=} {--extends=}';

    protected $description = 'Create page';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $dir = $this->option('dir') ?? 'Pages';
        $extends = $this->option('extends') ?? 'Page';

        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        if(str($className)->contains('/'))
            $dir = str($className)
                ->explode('/')
                ->tap(function (Collection $data) use (&$className): void {
                    $className = $data->last();
                })
                ->slice(0, -1)
                ->values()
                ->prepend('Pages')
                ->implode('/');

        $page = $this->getDirectory() . "/$dir/$className.php";

        if(! is_dir($this->getDirectory() . "/$dir")) {
            $this->makeDir($this->getDirectory() . "/$dir");
        }

        $stub = $this->option('extends') ? 'CrudPage' : 'Page';

        $this->copyStub($stub, $page, [
            '{namespace}' => MoonShine::namespace('\\' . str_replace('/', '\\', $dir)),
            'DummyPage' => $className,
            'DummyTitle' => $className,
            '{extendShort}' => $extends,
        ]);

        outro(
            "$className was created: " . str_replace(
                base_path(),
                '',
                $page
            )
        );

        return self::SUCCESS;
    }
}
