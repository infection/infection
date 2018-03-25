<?php

namespace Namespace_\Test;

use Namespace_\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_count()
    {
        $sourceClass = new SourceClass();
        $this->assertSame(1, $sourceClass->count());
    }
}
