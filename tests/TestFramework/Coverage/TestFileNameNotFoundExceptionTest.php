<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage;

use Infection\TestFramework\Coverage\TestFileNameNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TestFileNameNotFoundExceptionTest extends TestCase
{
    public function test_from_fqn(): void
    {
        $exception = TestFileNameNotFoundException::notFoundFromFQN('Foo\Bar');

        $this->assertInstanceOf(TestFileNameNotFoundException::class, $exception);
        $this->assertSame('For FQCN: Foo\Bar', $exception->getMessage());
    }
}
