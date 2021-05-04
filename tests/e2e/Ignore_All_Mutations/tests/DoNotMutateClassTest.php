<?php

namespace Ignore_All_Mutations\Test;

use Ignore_All_Mutations\DoNotMutateClass;
use PHPUnit\Framework\TestCase;

class DoNotMutateClassTest extends TestCase
{
    public function test_divide()
    {
        $sourceClass = new DoNotMutateClass();
        $this->assertGreaterThan(0.0, $sourceClass->divide(2, 2));
    }

}
