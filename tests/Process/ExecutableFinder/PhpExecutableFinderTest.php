<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\ExecutableFinder;

use Infection\Process\ExecutableFinder\PhpExecutableFinder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpExecutableFinderTest extends TestCase
{
    public function test_it_finds_needed_args(): void
    {
        $finder = new PhpExecutableFinder();

        if ('phpdbg' === \PHP_SAPI) {
            $this->assertSame(['-qrr'], $finder->findArguments());

            return;
        }

        $this->assertSame([], $finder->findArguments());
    }
}
