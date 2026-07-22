<?php

declare(strict_types=1);

namespace GitDiffFromSubdirectory\Tests;

use GitDiffFromSubdirectory\SourceClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceClass::class)]
final class SourceClassTest extends TestCase
{
    public function test_it_returns_true(): void
    {
        $this->assertTrue((new SourceClass())->returnsTrue());
    }
}
