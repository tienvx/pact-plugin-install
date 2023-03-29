<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\NoPlugin;

class UpdateNoPluginTest extends CommandNoPluginTestCase
{
    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['update',  '--no-plugins']);
    }
}
