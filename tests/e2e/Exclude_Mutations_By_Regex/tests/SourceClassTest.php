<?php

namespace Exclude_Mutations_By_Regex\Test;

use Exclude_Mutations_By_Regex\Logger;
use Exclude_Mutations_By_Regex\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass(new Logger());
        $this->assertSame('hello', $sourceClass->hello());
    }
}
