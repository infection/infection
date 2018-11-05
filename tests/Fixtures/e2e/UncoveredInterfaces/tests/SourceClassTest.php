<?php

namespace UncoveredInterfaces\Test;

use UncoveredInterfaces\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }

    public function test_code()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('301', $sourceClass->doSomething()->hello());
        $this->assertSame('200', $sourceClass->doSomething(200)->hello());
    }
}
