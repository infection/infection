<?php

namespace Namespace_\Test;

use Namespace_\SourceClass;

class SourceClassTest extends \PHPUnit\Framework\TestCase
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
