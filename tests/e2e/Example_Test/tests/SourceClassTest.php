<?php

namespace ExampleTest\Test;

use ExampleTest\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }

    public function test_this_test_case_kills_nothing(): void
    {
        $sourceClass = new SourceClass();
        $sourceClass->hello();

        $this->assertTrue(true);
    }
}
