<?php

declare(strict_types=1);

namespace Infection\E2ETests\GitDiffBase\Tests;

use Infection\E2ETests\GitDiffBase\SourceClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceClass::class)]
final class SourceClassTest extends TestCase
{
    public function test_it_returns_true(): void
    {
        $source = new SourceClass();

        $this->assertTrue($source->returnsTrue());
    }
}
