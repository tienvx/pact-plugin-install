<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\WorkingDir;

class UpdateWorkingDirTest extends CommandWorkingDirTestCase
{
    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['update',  '-d', self::getPathToTestDir()]);
    }
}
