<?php

namespace Syntax_Error_PHPUnit\Test;

use Syntax_Error_PHPUnit\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->bar());
    }
}
