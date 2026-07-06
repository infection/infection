<?php

namespace PHPStan_Integration\Test;

use PHPStan_Integration\SourceClass;
use PHPUnit\Framework\TestCase;
use function array_key_first;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        // This is to alter the PHPUnit output to try our reading of the memory
        // consumption of executing PHPUnit.
        echo "Memory: 16.00 MB\n";

        $sourceClass = new SourceClass();

        $list = $sourceClass->makeAList(['a' => 'b', 'c' => 'd']);

        $this->assertCount(2, $list);

        // try to kill to not run phpstan
//        $this->assertSame(0, array_key_first($list));
    }
}
