<?php

declare(strict_types=1);

namespace Infected;

use Infected\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_it_gets_config(): void
    {
        $source = new SourceClass();

        $result = $source->getConfig();

        self::assertSame('value', $result->key);
    }
}
