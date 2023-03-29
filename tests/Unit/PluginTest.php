<?php

namespace Tienvx\PactPluginInstall\Tests\Unit;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\InstallationManager;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Tienvx\PactPluginInstall\Plugin;
use VirtualFileSystem\FileSystem as VFileSystem;

class PluginTest extends TestCase
{
    private Config|MockObject $config;
    private InstallationManager|MockObject $manager;
    private PackageInterface|MockObject $package;
    private Filesystem|MockObject $filesystem;
    private Plugin $plugin;
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    private VFileSystem $vfs;
    private string $packageInstallPath = '/path/to/vendor/user/package';
    private string $binDir = '/path/to/vendor/bin';

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->manager = $this->createMock(InstallationManager::class);
        $this->package = $this->createMock(PackageInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->plugin = new Plugin($this->filesystem);
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->vfs = new VFileSystem();
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([
            PackageEvents::POST_PACKAGE_INSTALL => 'installPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'updatePackage',
            ScriptEvents::POST_INSTALL_CMD => 'installPackages',
            ScriptEvents::POST_UPDATE_CMD => 'installPackages',
        ], Plugin::getSubscribedEvents());
    }

    public function testActivate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->activate($this->composer, $this->io);
    }

    public function testDeactivate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->deactivate($this->composer, $this->io);
    }

    public function testUninstall(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->uninstall($this->composer, $this->io);
    }

    /**
     * @dataProvider composerProvider
     */
    public function testInstallPackages(?string $binDir, string $type, bool $symlinked): void
    {
        $this->createFiles();
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $this->composer
            ->expects($this->once())
            ->method('getPackage')
            ->willReturn($rootPackage);
        $rootPackage
            ->expects($this->once())
            ->method('getType')
            ->willReturn('project');
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $this->composer
            ->expects($this->once())
            ->method('getRepositoryManager')
            ->willReturn($repositoryManager);
        $localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $repositoryManager
            ->expects($this->once())
            ->method('getLocalRepository')
            ->willReturn($localRepository);
        $packages = [
            $library = $this->createMock(PackageInterface::class),
            $this->package,
        ];
        $localRepository
            ->expects($this->once())
            ->method('getCanonicalPackages')
            ->willReturn($packages);
        $library
            ->expects($this->once())
            ->method('getType')
            ->willReturn('library');
        $this->expectComposer($type, $binDir);
        $this->expectPackage($type);
        $this->expectFilesystem($symlinked);
        $event = new Event(
            'name',
            $this->composer,
            $this->io,
        );
        $this->plugin->activate($this->composer, $this->io);
        $this->plugin->installPackages($event);
    }

    /**
     * @dataProvider composerProvider
     */
    public function testInstallPackage(?string $binDir, string $type, bool $symlinked): void
    {
        $this->createFiles();
        $this->expectComposer($type, $binDir);
        $this->expectPackage($type);
        $this->expectFilesystem($symlinked);
        $event = new PackageEvent(
            'name',
            $this->composer,
            $this->io,
            false,
            $this->createMock(RepositoryInterface::class),
            [],
            new InstallOperation($this->package)
        );
        $this->plugin->activate($this->composer, $this->io);
        $this->plugin->installPackage($event);
    }

    /**
     * @dataProvider composerProvider
     */
    public function testUpdatePackage(?string $binDir, string $type, bool $symlinked): void
    {
        $this->createFiles();
        $this->expectComposer($type, $binDir);
        $this->expectPackage($type);
        $this->expectFilesystem($symlinked);
        $initial = $this->createMock(PackageInterface::class);
        $event = new PackageEvent(
            'name',
            $this->composer,
            $this->io,
            false,
            $this->createMock(RepositoryInterface::class),
            [],
            new UpdateOperation($initial, $this->package)
        );
        $this->plugin->activate($this->composer, $this->io);
        $this->plugin->updatePackage($event);
    }

    private function expectPackage(string $type): void
    {
        $this->package
            ->expects($this->once())
            ->method('getType')
            ->willReturn($type);
    }

    private function expectComposer(string $type, ?string $binDir): void
    {
        $this->composer
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->config);
        $this->config
            ->expects($this->any())
            ->method('get')
            ->with('bin-dir')
            ->willReturn($binDir);
        $getInstallPathCalled = Plugin::PACKAGE_TYPE === $type && $binDir;
        $this->composer
            ->expects($this->exactly((int) $getInstallPathCalled))
            ->method('getInstallationManager')
            ->willReturn($this->manager);
        $this->manager
            ->expects($this->exactly((int) $getInstallPathCalled))
            ->method('getInstallPath')
            ->with($this->package)
            ->willReturn($this->vfs->path($this->packageInstallPath));
    }

    private function expectFilesystem(bool $symlinked): void
    {
        $series = [
            [
                $this->vfs->path($this->packageInstallPath).\DIRECTORY_SEPARATOR.'bin/pacts/name1',
                $this->binDir.\DIRECTORY_SEPARATOR.Plugin::PACT_PLUGINS_DIR.\DIRECTORY_SEPARATOR.'name1-1.2.3',
                false,
            ],
            [
                $this->vfs->path($this->packageInstallPath).\DIRECTORY_SEPARATOR.'pact-plugins/name2',
                $this->binDir.\DIRECTORY_SEPARATOR.Plugin::PACT_PLUGINS_DIR.\DIRECTORY_SEPARATOR.'name2-2.1.0',
                false,
            ],
        ];
        $this->filesystem
            ->expects($this->exactly($symlinked ? 2 : 0))
            ->method('symlink')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);
            });
    }

    private function createFiles(): void
    {
        $files = [
            [
                'path' => 'bin/pacts/name1/pact-plugin.json',
                'name' => 'name1',
                'version' => '1.2.3',
            ],
            [
                'path' => 'pact-plugins/name2/pact-plugin.json',
                'name' => 'name2',
                'version' => '2.1.0',
            ],
            [
                'path' => 'pact-plugins/name3/pact-plugin.json',
                'name' => 'name3',
                'version' => null,
            ],
            [
                'path' => 'name4/pact-plugin.json',
                'name' => null,
                'version' => '1.1.1',
            ],
            [
                'path' => 'test.json',
                'name' => 'test',
                'version' => '9.9.9',
            ],
        ];
        foreach ($files as $file) {
            $path = $this->packageInstallPath.\DIRECTORY_SEPARATOR.$file['path'];
            $dir = \dirname($path);
            $fs = new Filesystem();
            $fs->mkdir($this->vfs->path($dir));
            $content = json_encode([
                'name' => $file['name'],
                'version' => $file['version'],
            ]);
            $this->vfs->createFile($path, $content);
        }
    }

    public function composerProvider(): array
    {
        return [
            [null, Plugin::PACKAGE_TYPE, false],
            [$this->binDir, 'library', false],
            [$this->binDir, Plugin::PACKAGE_TYPE, true],
        ];
    }
}
