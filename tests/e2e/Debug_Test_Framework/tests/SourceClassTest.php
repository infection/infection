<?php

declare(strict_types=1);

namespace Debug_Test_Framework\Tests;

use Debug_Test_Framework\SourceClass;
use PHPUnit\Framework\TestCase;

final class SourceClassTest extends TestCase
{
    public function test_it_calculates(): void
    {
        $this->assertSame(3, (new SourceClass())->calculate());
    }
}
