<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Generator;
use Infection\Configuration\Entry\Source;
use PHPUnit\Framework\TestCase;

final class SourceTest extends TestCase
{
    use SourceAssertions;

    /**
     * @dataProvider valuesProvider
     */
    public function test_it_can_be_instantiated(array $directories, array $excludes): void
    {
        $source = new Source($directories, $excludes);

        $this->assertSourceStateIs(
            $source,
            $directories,
            $excludes
        );
    }

    public function valuesProvider(): Generator
    {
        yield 'minimal' => [
            [],
            [],
        ];

        yield 'complete' => [
            ['src', 'lib'],
            ['fixtures', 'tests'],
        ];
    }
}
