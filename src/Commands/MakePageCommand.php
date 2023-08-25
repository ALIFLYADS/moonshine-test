<?php

declare(strict_types=1);

namespace MoonShine\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use MoonShine\MoonShine;

use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

class MakePageCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:page {className?}';

    protected $description = 'Create page';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $page = $this->getDirectory() . "/Pages/$className.php";

        $this->copyStub('Page', $page, [
            '{namespace}' => MoonShine::namespace('\Pages'),
            'DummyPage' => $className,
            'DummyTitle' => $className,
        ]);

        outro(
            "$className was created: " . str_replace(
                base_path(),
                '',
                $page
            )
        );
    }
}
