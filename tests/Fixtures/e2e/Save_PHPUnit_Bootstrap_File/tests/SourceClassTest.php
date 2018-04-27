<?php

namespace Namespace_\Test;

use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new \CustomAutoloadedClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
