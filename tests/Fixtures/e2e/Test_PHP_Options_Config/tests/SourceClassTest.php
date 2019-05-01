<?php

namespace Test_PHP_Options_Config\Test;

use Test_PHP_Options_Config\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        if (ini_get('short_open_tag') !== 'Test') {
            echo 'Failure to pass php option';
            die(1);
        }
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
