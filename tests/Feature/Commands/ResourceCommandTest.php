<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\MakeResourceCommand;
use MoonShine\MenuManager\MenuItem;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(MakeResourceCommand::class)]
#[Group('commands')]
final class ResourceCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful file created')]
    public function successfulCreated(): void
    {
        $reflector = new \ReflectionClass(moonshineConfig()->getLayout());

        $name = 'DeleteMeResource';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Resources/DeleteMe/$file";
        $layoutPath = $reflector->getFileName();

        @unlink($path);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeResourceCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Type', 'ModelResource')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);

        $layoutContent = file_get_contents($layoutPath);

        $this->assertStringContainsString(MenuItem::class, $layoutContent);
        $this->assertStringContainsString('DeleteMeResource', $layoutContent);
    }

    #[Test]
    #[TestDox('it successful file with pages created')]
    public function successfulWithPagesCreated(): void
    {
        $name = 'DeleteMeResource';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Resources/DeleteMe/$file";
        $path1 = __DIR__ . "/../../../app/MoonShine/Resources/DeleteMe/Pages/DeleteMeIndexPage.php";
        $path2 = __DIR__ . "/../../../app/MoonShine/Resources/DeleteMe/Pages/DeleteMeFormPage.php";
        $path3 = __DIR__ . "/../../../app/MoonShine/Resources/DeleteMe/Pages/DeleteMeDetailPage.php";

        @unlink($path);
        @unlink($path1);
        @unlink($path2);
        @unlink($path3);

        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist($path1);
        $this->assertFileDoesNotExist($path2);
        $this->assertFileDoesNotExist($path3);

        $this->artisan(MakeResourceCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Type', 'ModelResource')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->expectsOutputToContain(
                "DeleteMeIndexPage was created"
            )
            ->expectsOutputToContain(
                "DeleteMeFormPage was created"
            )
            ->expectsOutputToContain(
                "DeleteMeDetailPage was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertFileExists($path1);
        $this->assertFileExists($path2);
        $this->assertFileExists($path3);
    }

    #[Test]
    #[TestDox('it successful file created in sub folder')]
    public function successfulCreatedInSubFolder(): void
    {
        $dir = 'Test';
        $name = 'DeleteMeResource';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Resources/DeleteMe/$dir/$file";

        @unlink($path);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeResourceCommand::class, [
            'className' => "$dir/$name",
        ])
            ->expectsQuestion('Type', 'ModelResource')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
    }
}
