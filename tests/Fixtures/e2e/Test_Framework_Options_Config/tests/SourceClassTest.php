<?php

namespace Test_Framework_Options_Config\Test;

use Test_Framework_Options_Config\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        if (
            !in_array('--strict-global-state', $GLOBALS['argv'], true)
        ) {
            var_dump($GLOBALS['argv']);
            echo 'Failure to pass framework option';
            die(1);
        }
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
