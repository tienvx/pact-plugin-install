<?php

namespace Tienvx\PactPluginInstall\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class CommandTestCase extends TestCase
{
    private static ?string $origDir;
    protected static ?string $testDir;

    protected static function getComposerJson(): array
    {
        return [
            'name' => 'test/project',
            'repositories' => [
                'pact-plugin-install' => [
                    'type' => 'path',
                    'url' => self::getPluginSourceDir(),
                ],
                'library' => [
                    'type' => 'path',
                    'url' => self::getLibraryPath(),
                    'options' => [
                        'symlink' => false,
                    ],
                ],
            ],
            'require' => [
                'tienvx/pact-plugin-install' => '@dev',
                'test/library' => '@dev',
            ],
            'minimum-stability' => 'dev',
            'config' => [
                'allow-plugins' => [
                    'tienvx/pact-plugin-install' => true,
                ],
            ],
        ];
    }

    public static function setUpBeforeClass(): void
    {
        self::initTestProject();
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanTestProjectDir();
    }

    protected function setUp(): void
    {
        self::cleanDir(self::getPathToTestDir('vendor/bin'));
    }

    protected static function getPathToTestDir(string $path = ''): string
    {
        return self::$testDir.\DIRECTORY_SEPARATOR.$path;
    }

    protected function runComposerCommandAndAssert(array $command): void
    {
        $this->assertFiles($this->shouldExistBeforeCommand());
        $this->runComposer($command);
        $this->assertFiles($this->shouldExistAfterCommand());
    }

    protected function shouldExistBeforeCommand(): bool
    {
        return false;
    }

    protected function shouldExistAfterCommand(): bool
    {
        return true;
    }

    protected function assertFiles(bool $exist = true): void
    {
        $plugins = [
            'json' => '0.0.2',
            'xml' => '0.3.5',
        ];
        foreach ($plugins as $name => $version) {
            $originDir = self::getPathToTestDir("vendor/test/library/bin/pact-plugins/{$name}");
            $targetDir = self::getPathToTestDir("vendor/bin/pact-plugins/{$name}-{$version}");
            $this->assertSame($exist, is_link($targetDir));
            if ($exist) {
                $this->assertTrue($originDir === readlink($targetDir));
            }
        }
    }

    /**
     * Create a temp folder with a "composer.json" file and chdir() into it if needed.
     */
    protected static function initTestProject(): void
    {
        self::$origDir = getcwd();
        $testDir = getenv('USE_TEST_PROJECT');
        if (\is_string($testDir)) {
            self::$testDir = $testDir;
            @unlink(self::getPathToTestDir('composer.lock'));
        } else {
            self::$testDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'assetplg-'.md5(__DIR__.time().random_int(0, 10000));
            self::cleanDir(self::$testDir);
        }

        if (!is_dir(self::$testDir)) {
            mkdir(self::$testDir);
        }
        file_put_contents(self::getPathToTestDir('composer.json'), json_encode(static::getComposerJson(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        static::shouldChangeDir() && chdir(self::$testDir);
    }

    protected static function cleanTestProjectDir(): void
    {
        if (self::$testDir) {
            static::shouldChangeDir() && chdir(self::$origDir);
            self::$origDir = null;

            if (getenv('USE_TEST_PROJECT')) {
                fwrite(\STDERR, sprintf("\n\nTest project location (%s): %s\n", self::class, self::$testDir));
            } else {
                self::cleanDir(self::$testDir);
            }
            self::$testDir = null;
        }
    }

    protected static function shouldChangeDir(): bool
    {
        return true;
    }

    private function runComposer(array $command): void
    {
        $process = new Process([self::getComposerPath(), ...$command, '-v']);
        $process->run(getenv('DEBUG_COMPOSER') ? function ($type, $buffer) {
            echo $buffer;
        } : null);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->assertComposerErrorOutput($process->getErrorOutput());
    }

    protected function assertComposerErrorOutput(string $output): void
    {
    }

    protected static function cleanDir(string $dir): void
    {
        $process = Process::fromShellCommandline(
            \PHP_OS_FAMILY === 'Windows'
            ? 'if exist "${:DIR}" ( rm -rf "${:DIR}" )'
            : 'if [ -d "${:DIR}" ]; then rm -rf "${:DIR}" ; fi'
        );
        $process->run(null, ['DIR' => $dir]);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    protected static function getComposerPath(): string
    {
        return realpath(__DIR__.'/../../vendor/bin/composer');
    }

    private static function getPluginSourceDir(): string
    {
        return realpath(__DIR__.'/../..');
    }

    private static function getFixturesPath(): string
    {
        return realpath(__DIR__.'/../Fixtures');
    }

    private static function getLibraryPath(): string
    {
        return realpath(self::getFixturesPath().'/library');
    }
}
