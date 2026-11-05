<?php

namespace DiffFile\Test;

use DiffFile\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }

    public function test_goodbye()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('goodbye', $sourceClass->goodbye());
    }
}
