<?php

namespace Namespace_\Test;

use Namespace_\SourceClass;

class SourceClassTest extends \PHPUnit\Framework\TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
