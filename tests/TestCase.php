<?php

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Testing\FakeShell;

class TestCase extends Orchestra\Testbench\TestCase
{
    protected $app;

    protected array $temporaryDirectories = [];

    protected function getEnvironmentSetUp($app)
    {
        $app->make('Illuminate\Contracts\Http\Kernel');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Grafite\Maintenance\GrafiteMaintenanceProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryDirectories as $directory) {
            $this->app?->make('files')->deleteDirectory($directory);
        }

        parent::tearDown();
    }

    protected function fakeShell(): FakeShell
    {
        $shell = new FakeShell();

        $this->app->instance(Shell::class, $shell);

        return $shell;
    }

    protected function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/fixtures/' . $name);
    }

    /**
     * Point the application's base path at a fresh temporary directory.
     */
    protected function temporaryBasePath(array $files = []): string
    {
        $path = sys_get_temp_dir() . '/grafite-maintenance-' . bin2hex(random_bytes(6));
        mkdir($path . '/public', 0777, true);

        $this->temporaryDirectories[] = $path;

        foreach ($files as $file => $contents) {
            if (! is_dir(dirname($path . '/' . $file))) {
                mkdir(dirname($path . '/' . $file), 0777, true);
            }

            file_put_contents($path . '/' . $file, $contents);
        }

        $this->app->setBasePath($path);
        $this->app->useEnvironmentPath($path);

        return $path;
    }
}
