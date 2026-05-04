<?php

namespace Mago_Integration\Test;

use Mago_Integration\SourceClass;
use PHPUnit\Framework\TestCase;
use function array_key_first;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();

        $list = $sourceClass->makeAList(['a' => 'b', 'c' => 'd']);

        $this->assertCount(2, $list);

        // try to kill to not run mago
//        $this->assertSame(0, array_key_first($list));
    }
}
