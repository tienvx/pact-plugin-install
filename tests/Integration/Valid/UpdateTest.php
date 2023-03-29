<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\Valid;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

class UpdateTest extends CommandTestCase
{
    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['update']);
    }
}
