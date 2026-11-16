<?php

namespace PHPUnit13CustomConfigDirWithoutSource\Test;

use PHPUnit13CustomConfigDirWithoutSource\ExtraSourceClass;
use PHPUnit13CustomConfigDirWithoutSource\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello(): void
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }

    public function test_extra_source_hello(): void
    {
        $sourceClass = new ExtraSourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
