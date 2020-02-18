<?php

namespace Composer_Config_Custom_Vendor_Dir\Test;

use Composer_Config_Custom_Vendor_Dir\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
