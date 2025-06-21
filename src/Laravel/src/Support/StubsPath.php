<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class StubsPath
{
    public string $dir;

    public string $namespace;

    public string $basename;

    public string $name;

    public function __construct(
        private readonly string $path,
        private ?string $ext = null,
    ) {
        $this->ext ??= File::extension($this->path);

        $pathWithExt = Str::of($this->path)
            ->replace('\\', '/')
            ->replaceLast('.' . $this->ext, '')
            ->append('.' . $this->ext)
            ->value();

        $this->basename = Str::of($pathWithExt)->basename()->value();
        $this->name = str_replace('.' . $this->ext, '', $this->basename);

        $this->dir = Str::of($pathWithExt)
            ->dirname()
            ->trim('/')
            ->remove('.')
            ->value();

        $this->namespace = Str::of($this->dir)
            ->replace('/', '\\')
            ->value();
    }

    public function prependNamespace(string $path): self
    {
        $this->namespace = trim(
            $path . str_replace('/', '\\', "\\$this->namespace"),
            '\\'
        );

        return $this;
    }

    public function prependDir(string $path): self
    {
        $this->dir = trim(
            rtrim($path, '/') . '/' . $this->dir,
            '/'
        );

        return $this;
    }

    public function getPath(): string
    {
        return $this->dir . '/' . $this->basename;
    }

    public function getClassString(): string
    {
        return "$this->namespace\\$this->name::class";
    }
}
