<?php

namespace PHPStan_Custom_Executable_Path\Test;

use PHPStan_Custom_Executable_Path\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
