<?php

namespace ProvideExistingCoverage\Test;

use PHPUnit\Framework\TestCase;
use ProvideExistingCoverage\SourceClass;

class SourceClassTest extends TestCase
{
    public function test_it_adds(): void
    {
        file_put_contents(__DIR__ . '/../has_run', 'phpunit');
        $source = new SourceClass();
        $this->assertSame(3, $source->add(1, 2));
    }

    public function test_it_returns_true(): void
    {
        $source = new SourceClass();
        $this->assertTrue($source->isTrue());
    }
}
