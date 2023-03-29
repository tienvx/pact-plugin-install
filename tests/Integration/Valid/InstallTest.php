<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\Valid;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

class InstallTest extends CommandTestCase
{
    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['install']);
    }
}
