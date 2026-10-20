<?php

namespace PHPUnit13CustomConfigDir\Test;

use PHPUnit13CustomConfigDir\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello(): void
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
