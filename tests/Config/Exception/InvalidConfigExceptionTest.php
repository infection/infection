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

/**
 * @internal
 */
final class InvalidConfigExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $exception = new InvalidConfigException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function test_invalid_json_creates_exception(): void
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

    public function test_invalid_mutator_creates_exception(): void
    {
        $wrongMutator = 'NonExistent Mutator';

        $exception = InvalidConfigException::invalidMutator($wrongMutator);

        $this->assertInstanceOf(InvalidConfigException::class, $exception);

        $expected = sprintf(
            'The "%s" mutator/profile was not recognized.',
            $wrongMutator
        );

        $this->assertSame($expected, $exception->getMessage());
    }

    public function test_invalid_profile_creates_exception(): void
    {
        $configFile = '@hello';
        $errorMessage = 'Wrong Mutator';

        $exception = InvalidConfigException::invalidProfile(
            $configFile,
            $errorMessage
        );

        $this->assertInstanceOf(InvalidConfigException::class, $exception);

        $expected = sprintf(
            'The "%s" profile contains the "%s" mutator which was not recognized.',
            $configFile,
            $errorMessage
        );

        $this->assertSame($expected, $exception->getMessage());
    }
}
