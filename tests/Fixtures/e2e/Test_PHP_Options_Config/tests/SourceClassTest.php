<?php

namespace Test_PHP_Options_Config\Test;

use PHPUnit\Framework\TestCase;
use Test_PHP_Options_Config\SourceClass;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        if (ini_get('memory_limit') !== 'Test') {
            $this->fail(
                sprintf(
                    "Failure to pass php option (ini memory_limit: '%s')",
                    ini_get('memory_limit')
                )
            );
        }

        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
