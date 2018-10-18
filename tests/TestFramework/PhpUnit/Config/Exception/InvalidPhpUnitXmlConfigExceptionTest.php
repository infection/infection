<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config\Exception;

use Infection\TestFramework\PhpUnit\Config\Exception\InvalidPhpUnitXmlConfigException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InvalidPhpUnitXmlConfigExceptionTest extends TestCase
{
    public function test_for_root_node(): void
    {
        $exception = InvalidPhpUnitXmlConfigException::byRootNode();

        $this->assertInstanceOf(InvalidPhpUnitXmlConfigException::class, $exception);

        $this->assertSame('phpunit.xml does not contain a valid PHPUnit configuration.', $exception->getMessage());
    }

    public function test_for_xsd_schema(): void
    {
        $exception = InvalidPhpUnitXmlConfigException::byXsdSchema('Invalid');

        $this->assertInstanceOf(InvalidPhpUnitXmlConfigException::class, $exception);

        $this->assertContains('phpunit.xml file does not pass XSD schema validation.', $exception->getMessage());
    }
}
