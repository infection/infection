<?php

namespace ProvideExistingCoverage\Test;

use PHPUnit\Framework\TestCase;
use ProvideExistingCoverage\SourceClass;

/** @coversNothing */
class CanaryTest extends TestCase
{
    public function test_it_makes_a_file(): void
    {
        $this->assertSame(7, file_put_contents(__DIR__ . '/../has_run', 'phpunit'));
    }
}
