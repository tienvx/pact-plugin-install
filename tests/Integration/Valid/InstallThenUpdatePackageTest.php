<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\Valid;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

class InstallThenUpdatePackageTest extends CommandTestCase
{
    /**
     * @testWith [["install"]]
     *           [["update", "test/library"]]
     */
    public function testSymlink(array $command): void
    {
        $this->runComposerCommandAndAssert($command);
    }
}
