<?php

namespace PHPUnit13CustomConfigDirWithoutSource\Test;

use PHPUnit13CustomConfigDirWithoutSource\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello(): void
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
