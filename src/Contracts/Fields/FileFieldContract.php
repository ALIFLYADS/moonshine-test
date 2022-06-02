<?php

namespace Leeto\MoonShine\Contracts\Fields;

interface FileFieldContract
{
    public function disk(string $disk): static;

    public function getDisk(): string;

    public function dir(string $dir): static;

    public function getDir(): string;

    public function allowedExtensions(array $allowedExtensions): static;

    public function getAllowedExtensions(): array;

    public function isAllowedExtension(string $extension): bool;

    public function disableDownload(): static;

    public function canDownload(): bool;

    public function removable(): static;

    public function isRemovable(): bool;
}