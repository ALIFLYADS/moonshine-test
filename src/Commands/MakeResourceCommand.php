<?php

declare(strict_types=1);

namespace MoonShine\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{info, outro, select, text};

use MoonShine\MoonShine;

class MakeResourceCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:resource {name?} {--m|model=} {--t|title=}';

    protected $description = 'Create resource';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $name = str(
            text(
                'Name',
                'ArticleResource',
                $this->argument('name') ?? '',
                required: true,
            )
        );

        $dir = $name->ucfirst()
            ->remove('Resource', false)
            ->value();

        $name = $name->ucfirst()
            ->replace(['resource', 'Resource'], '')
            ->value();

        $model = $this->qualifyModel($this->option('model') ?? $name);
        $title = $this->option('title') ?? str($name)->singular()->plural()->value();

        $resource = $this->getDirectory() . "/Resources/{$name}Resource.php";

        $stub = select('Resource type', [
            'ModelResourceDefault' => 'Default model resource',
            'ModelResourceSeparate' => 'Separate model resource',
            'ModelResourceWithPages' => 'Model resource with pages',
            'Resource' => 'Empty resource',
        ], 'ModelResourceDefault');

        $replaceData = [
            '{namespace}' => MoonShine::namespace('\Resources'),
            '{model-namespace}' => $model,
            '{model}' => class_basename($model),
            'DummyTitle' => $title,
            'Dummy' => $name,
        ];

        if($stub === 'ModelResourceWithPages') {
            $pageDir = "Pages/$dir";
            $pageData = fn (string $name): array => [
                'className' => "$dir$name",
                '--dir' => $pageDir,
                '--extends' => $name,
            ];

            $this->call(MakePageCommand::class, $pageData('IndexPage'));
            $this->call(MakePageCommand::class, $pageData('FormPage'));
            $this->call(MakePageCommand::class, $pageData('DetailPage'));

            $pageNamespace = fn (string $name): string => MoonShine::namespace(
                str_replace('/', '\\', "\\$pageDir\\$dir$name")
            );

            $replaceData = [
                '{indexPage}' => "{$dir}IndexPage",
                '{formPage}' => "{$dir}FormPage",
                '{detailPage}' => "{$dir}DetailPage",
                '{index-page-namespace}' => $pageNamespace('IndexPage'),
                '{form-page-namespace}' => $pageNamespace('FormPage'),
                '{detail-page-namespace}' => $pageNamespace('DetailPage'),
            ] + $replaceData;
        }

        $this->copyStub($stub, $resource, $replaceData);

        info(
            "{$name}Resource file was created: " . str_replace(
                base_path(),
                '',
                $resource
            )
        );

        outro('Now register resource in menu');

        return self::SUCCESS;
    }
}
