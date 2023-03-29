<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\WorkingDir;

class InstallWorkingDirTest extends CommandWorkingDirTestCase
{
    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['install', '-d', self::getPathToTestDir()]);
    }
}
