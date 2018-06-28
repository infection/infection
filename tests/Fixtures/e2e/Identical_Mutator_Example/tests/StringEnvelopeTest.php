<?php

namespace Namespace_\Test;

use PHPUnit\Framework\TestCase;
use Namespace_\StringEnvelope;

class StringEnvelopeTest extends TestCase
{
    public function testHasSubstring()
    {
        $sourceClass = new StringEnvelope('The quick brown fox jumps over the lazy dog');
        $this->assertTrue($sourceClass->hasSubstring('quick'));
        $this->assertFalse($sourceClass->hasSubstring('slow'));
        // Test suite is incomplete without this test; it was left out intentionally
        //$this->assertTrue($sourceClass->hasSubstring('The'));
    }

    public function testHasNotSubstring()
    {
        $sourceClass = new StringEnvelope('The quick brown fox jumps over the lazy dog');
        $this->assertTrue($sourceClass->hasNotSubstring('slow'));
        $this->assertFalse($sourceClass->hasNotSubstring('quick'));
        // Test suite is incomplete without this test; it was left out intentionally
        //$this->assertFalse($sourceClass->hasNotSubstring('The'));
    }
}
