<?php

namespace YieldValue\Test;

use YieldValue\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $result = $sourceClass->hello();

        foreach ($result as $key => $value) {
            self::assertSame('key', $key);
            self::assertSame('value', $value);
        }
    }
}
