<?php

namespace Method_Attribute_Signature\Test;

use Method_Attribute_Signature\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_without_attribute()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->withoutAttribute());
    }

    public function test_with_attribute_on_its_own_line()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->withAttributeOnItsOwnLine());
    }

    public function test_with_attribute_on_the_same_line()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->withAttributeOnTheSameLine());
    }
}
