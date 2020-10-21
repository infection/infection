<?php

namespace PreMutantFilter\Test;

use PHPUnit\Framework\TestCase;
use PreMutantFilter\SourceClass;

class SourceClassTest extends TestCase
{
    public function test_hello(): void
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
