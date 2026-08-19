<?php

namespace Infection\E2ETests\QuietModePHPUnit\Tests;

use Infection\E2ETests\QuietModePHPUnit\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello(): void
    {
        $sourceClass = new SourceClass();

        $this->assertSame('hello', $sourceClass->hello());
    }
}
