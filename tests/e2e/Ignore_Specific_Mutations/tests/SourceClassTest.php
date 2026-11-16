<?php

declare(strict_types=1);

namespace Ignore_Specific_Mutations\Tests;

use Ignore_Specific_Mutations\SourceClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceClass::class)]
final class SourceClassTest extends TestCase
{
    public function test_selectively_ignored(): void
    {
        $sourceClass = new SourceClass();

        $this->assertSame(
            [
                false,
                true,
            ],
            $sourceClass->selectivelyIgnored(),
        );
    }

    public function test_not_ignored(): void
    {
        $sourceClass = new SourceClass();

        $this->assertSame(
            [
                false,
                true,
            ],
            $sourceClass->notIgnored(),
        );
    }
}
