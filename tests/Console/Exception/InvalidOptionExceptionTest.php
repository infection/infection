<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console\Exception;

use Infection\Console\Exception\InfectionException;
use Infection\Console\Exception\InvalidOptionException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InvalidOptionExceptionTest extends TestCase
{
    public function test_with_message()
    {
        $message = 'Error Message';

        $exception = InvalidOptionException::withMessage($message);

        $this->assertInstanceOf(InvalidOptionException::class, $exception);
        $this->assertInstanceOf(InfectionException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
