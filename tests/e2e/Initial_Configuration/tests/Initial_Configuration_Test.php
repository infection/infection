<?php

use Initial_Configuration\SourceClass;
use PHPUnit\Framework\TestCase;

class Initial_Configuration_Test extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
