<?php

namespace OutputStream;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test1(): void
    {
        $example = new Example();
        $result = $example->run();
        $this->assertTrue($result);
    }
}
