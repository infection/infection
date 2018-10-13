<?php

namespace MemoryLimit\Test;

use MemoryLimit\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_count()
    {
        $sourceClass = new SourceClass();
        $this->assertSame(1, $sourceClass->count());
    }
}
