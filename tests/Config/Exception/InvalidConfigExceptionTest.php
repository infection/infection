<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\Exception;

use Infection\Config\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;

final class InvalidConfigExceptionTest extends TestCase
{
    public function test_extends_runtime_exception()
    {
        $exception = new InvalidConfigException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function test_invalid_json_creates_exception()
    {
        $configFile = __DIR__ . '/../../../infection.json.dist';
        $errorMessage = 'That does not look right.';

        $exception = InvalidConfigException::invalidJson(
            $configFile,
            $errorMessage
        );

        $this->assertInstanceOf(InvalidConfigException::class, $exception);

        $expected = sprintf(
            'The configuration file "%s" does not contain valid JSON: %s.',
            $configFile,
            $errorMessage
        );

        $this->assertSame($expected, $exception->getMessage());
    }
}
