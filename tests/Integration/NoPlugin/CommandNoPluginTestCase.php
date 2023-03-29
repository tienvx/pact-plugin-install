<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\NoPlugin;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

abstract class CommandNoPluginTestCase extends CommandTestCase
{
    protected function shouldExistAfterCommand(): bool
    {
        return false;
    }
}
