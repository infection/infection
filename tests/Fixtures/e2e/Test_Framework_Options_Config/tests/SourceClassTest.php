<?php

namespace Test_Framework_Options_Config\Test;

use PHPUnit\Framework\TestCase;
use Test_Framework_Options_Config\SourceClass;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        if (
            !in_array('--strict-global-state', $GLOBALS['argv'], true)
        ) {
            $this->fail(
                sprintf(
                    'Failure to pass framework option (argv: %s)',
                    implode(' ', $GLOBALS['argv'])
                )
            );
        }

        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
