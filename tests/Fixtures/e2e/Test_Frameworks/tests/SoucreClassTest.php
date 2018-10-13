<?php

namespace TestFrameworks\Test;

use TestFrameworks\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{

    public function test_it_adds()
    {
        $source = new SourceClass();
        $this->assertSame(3, $source->add(1,2));
    }

    public function test_it_returns_true()
    {
        $source = new SourceClass();
        $this->assertTrue($source->isTrue());
    }
}
