<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\NoPlugin;

class InstallNoPluginTest extends CommandNoPluginTestCase
{
    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['install', '--no-plugins']);
    }
}
