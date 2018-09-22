<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Exception;

/**
 * @internal
 */
final class InvalidPhpUnitXmlConfigException extends \RuntimeException
{
    public static function byRootNode(): self
    {
        return new self('phpunit.xml does not contain a valid PHPUnit configuration.');
    }

    public static function byXsdSchema(string $libXmlErrorsString): self
    {
        return new self(sprintf('phpunit.xml file does not pass XSD schema validation. %s', $libXmlErrorsString));
    }
}
