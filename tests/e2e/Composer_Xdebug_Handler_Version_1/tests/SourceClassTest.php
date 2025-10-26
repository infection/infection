<?php

namespace Composer_Xdebug_Handler_Version_1\Test;

use Composer_Xdebug_Handler_Version_1\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
