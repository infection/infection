<?php

namespace Trait_Coverage\Test;

use Trait_Coverage\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello World!', $sourceClass->hello());
    }
    public function test_world()
    {
        $sourceClass = new SourceClass();
        $this->assertSame(' World!', $sourceClass->world());
    }

    public function test_add()
    {
        $sourceClass = new SourceClass();
        $this->assertSame(3, $sourceClass->add(1,2));
    }
}
