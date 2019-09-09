<?php


namespace CodeCoverageIgnore\Test;


use CodeCoverageIgnore\IgnoreMethod;
use PHPUnit\Framework\TestCase;

class IgnoreMethodTest extends TestCase
{
    public function test_get_three(): void
    {
        $method = new IgnoreMethod();
        $this->assertSame(3, $method->getThree());
    }

    public function test_foo(): void
    {
        $method = new IgnoreMethod();
        $this->assertSame('foo', $method->foo());
    }
}
