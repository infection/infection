<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console\Exception;

use Infection\Console\Exception\ConfigurationException;
use Infection\Console\Exception\InfectionException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConfigurationExceptionTest extends TestCase
{
    public function test_configuration_aborted()
    {
        $exception = ConfigurationException::configurationAborted();

        $this->assertInstanceOf(ConfigurationException::class, $exception);
        $this->assertInstanceOf(InfectionException::class, $exception);
        $this->assertSame(
            'Configuration aborted',
            $exception->getMessage()
        );
    }
}
