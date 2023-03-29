<?php

namespace Tienvx\PactPluginInstall;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    public const PACT_PLUGINS_DIR = 'pact-plugins';
    public const PACKAGE_TYPE = 'pact-plugin';
    private Composer $composer;

    public function __construct(private ?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'installPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'updatePackage',
            ScriptEvents::POST_INSTALL_CMD => 'installPackages',
            ScriptEvents::POST_UPDATE_CMD => 'installPackages',
        ];
    }

    public function installPackages(Event $event): void
    {
        $rootPackage = $this->composer->getPackage();
        $this->install($rootPackage);

        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        foreach ($localRepo->getCanonicalPackages() as $package) {
            $this->install($package);
        }
    }

    public function installPackage(PackageEvent $event): void
    {
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();
        $this->install($package);
    }

    public function updatePackage(PackageEvent $event): void
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getTargetPackage();
        $this->install($package);
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    private function install(PackageInterface $package): void
    {
        $binDir = $this->composer->getConfig()->get('bin-dir');
        if (self::PACKAGE_TYPE !== $package->getType() || !\is_string($binDir)) {
            return;
        }

        $finder = new Finder();
        $finder
            ->files()
            ->name('pact-plugin.json')
            ->in($this->composer->getInstallationManager()->getInstallPath($package));
        foreach ($finder as $file) {
            $meta = json_decode($file->getContents(), true);
            if (!\is_string($meta['name'] ?? null) || !\is_string($meta['version'] ?? null)) {
                continue;
            }
            $target = sprintf('%s-%s', $meta['name'], $meta['version']);
            $this->filesystem->symlink(
                $file->getPath(),
                implode(\DIRECTORY_SEPARATOR, [$binDir, self::PACT_PLUGINS_DIR, $target])
            );
        }
    }
}
