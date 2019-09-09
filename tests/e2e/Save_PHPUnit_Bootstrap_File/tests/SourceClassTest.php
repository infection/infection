<?php

namespace SavePhpUnitBoostrapFile\Test;

use PHPUnit\Framework\TestCase;
use SavePhpUnitBoostrapFile\CustomAutoloadedClass;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new CustomAutoloadedClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
