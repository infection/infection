<?php

namespace Exclude_By_Folder\Test;

use Exclude_By_Folder\ToExclude\SourceClassToBeExcluded;
use PHPUnit\Framework\TestCase;

class SourceClassToBeExcludedTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClassToBeExcluded();
        $this->assertSame('hello', $sourceClass->hello());
    }
}
