<?php

declare(strict_types=1);

namespace PCOVSourceOutsideWorkingDirectory\Tests;

use PCOVSourceOutsideWorkingDirectory\Shared\SharedClass;
use PCOVSourceOutsideWorkingDirectory\SourceClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceClass::class)]
#[CoversClass(SharedClass::class)]
final class SourceClassTest extends TestCase
{
    public function test_it_returns_true(): void
    {
        $this->assertTrue((new SourceClass())->returnsTrue());
        $this->assertTrue((new SharedClass())->returnsTrue());
    }
}
