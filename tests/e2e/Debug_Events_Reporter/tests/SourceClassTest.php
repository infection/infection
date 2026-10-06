<?php

declare(strict_types=1);

namespace Debug_Events_Reporter\Tests;

use Debug_Events_Reporter\SourceClass;
use PHPUnit\Framework\TestCase;

final class SourceClassTest extends TestCase
{
    public function test_it_calculates(): void
    {
        $source = new SourceClass();

        $this->assertSame(
            3,
            $source->calculate(),
            'The calculation must add both operands.',
        );
    }
}
