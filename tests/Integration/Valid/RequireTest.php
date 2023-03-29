<?php

namespace Tienvx\PactPluginInstall\Tests\Integration\Valid;

use Tienvx\PactPluginInstall\Tests\Integration\CommandTestCase;

class RequireTest extends CommandTestCase
{
    protected static function getComposerJson(): array
    {
        return [
            'require' => [
                'tienvx/pact-plugin-install' => '@dev',
            ],
        ] + parent::getComposerJson();
    }

    public function testSymlink(): void
    {
        $this->runComposerCommandAndAssert(['require', 'test/library']);
    }
}
