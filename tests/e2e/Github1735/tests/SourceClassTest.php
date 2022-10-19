<?php

namespace Github1735\Test;

use Github1735\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        self::assertTrue($sourceClass->hello()['class']->foo());
    }
}
