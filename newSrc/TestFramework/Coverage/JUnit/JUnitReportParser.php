<?php

declare(strict_types=1);

namespace newSrc\TestFramework\Coverage\JUnit;

use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;

/**
 * TODO: heavily inspired from JUnitTestFileDataProvider
 * @see JUnitTestFileDataProvider
 */
final class JUnitReportParser
{
    public function parse(string $fileName): PHPUnitXmlReport {
        // TODO: the implementation need to be lazy and streamed.
    }
}
