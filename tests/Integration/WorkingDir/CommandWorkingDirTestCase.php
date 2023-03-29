<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\WorkingDir;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

abstract class CommandWorkingDirTestCase extends CommandTestCase
{
    protected static function shouldChangeDir(): bool
    {
        return false;
    }
}
