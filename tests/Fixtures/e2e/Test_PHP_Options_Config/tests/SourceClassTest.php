<?php

namespace Test_PHP_Options_Config\Test;

use PHPUnit\Framework\TestCase;
use Test_PHP_Options_Config\SourceClass;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        if (ini_get('short_open_tag') !== 'Test') {
            $this->fail(
                sprintf(
                    "Failure to pass php option (ini short_open_tag: '%s')",
                    ini_get('short_open_tag')
                )
            );
        }

        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
