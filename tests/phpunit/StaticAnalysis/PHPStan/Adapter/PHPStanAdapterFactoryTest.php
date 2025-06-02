<?php

namespace Infection\Tests\StaticAnalysis\PHPStan\Adapter;

use Infection\StaticAnalysis\PHPStan\Adapter\PHPStanAdapterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(PHPStanAdapterFactory::class)]
final class PHPStanAdapterFactoryTest extends TestCase
{
    public function test_it_can_create_an_adapter(): void
    {
        $adapter = PHPStanAdapterFactory::create('/path/to/phpstan');

        $this->assertSame('PHPStan', $adapter->getName());
    }
}
