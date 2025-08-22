<?php

declare(strict_types=1);

namespace newSrc\TestFramework\Coverage\JUnit;

use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;

/**
 * TODO: heavily inspired from IndexXmlCoverageParser
 * @see IndexXmlCoverageParser
 */
final class PHPUnitXmlParser
{
    public function parse(string $fileName): PHPUnitXmlReport {
        // TODO: the implementation need to be lazy and streamed.
    }
}
