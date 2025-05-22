<?php

namespace PHPStan_Integration\Test;

use PHPStan_Integration\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();

        $list = $sourceClass->makeAList(['a' => 'b', 'c' => 'd']);

        $this->assertCount(2, $list);
    }
}
