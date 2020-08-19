<?php

namespace Min_MSI\Test;

use Min_MSI\UntestedClass;
use PHPUnit\Framework\TestCase;

class BorkedTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new UntestedClass();
        $sourceClass->hello();

        $this->assertTrue(true);
    }
}
