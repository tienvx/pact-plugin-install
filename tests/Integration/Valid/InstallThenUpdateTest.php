<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\Valid;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

class InstallThenUpdateTest extends CommandTestCase
{
    /**
     * @testWith ["install"]
     *           ["update"]
     */
    public function testSymlink(string $command): void
    {
        $this->runComposerCommandAndAssert([$command]);
    }
}
