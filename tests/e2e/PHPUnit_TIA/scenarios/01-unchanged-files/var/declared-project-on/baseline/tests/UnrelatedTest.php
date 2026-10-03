<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Tests;

use Infection\E2ETests\PHPUnitTIA\Unrelated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Unrelated::class)]
final class UnrelatedTest extends TestCase
{
    public function test_calculate(): void
    {
        $this->assertSame(2, (new Unrelated())->calculate(1, 2));
    }
}
