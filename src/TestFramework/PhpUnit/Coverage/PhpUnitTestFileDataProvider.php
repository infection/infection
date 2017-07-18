<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);

namespace Infection\TestFramework\PhpUnit\Coverage;

use Infection\TestFramework\Coverage\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\TestFileDataProvider;

class PhpUnitTestFileDataProvider implements TestFileDataProvider
{
    /**
     * @var string
     */
    private $jUnitFilePath;

    public function __construct(string $jUnitFilePath)
    {
        $this->jUnitFilePath = $jUnitFilePath;
    }

    public function getTestFileInfo(string $fullyQualifiedClassName): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($this->jUnitFilePath));
        $xPath = new \DOMXPath($dom);

        $nodes = $xPath->query(sprintf('//testsuite[@name="%s"]', $fullyQualifiedClassName));

        if ($nodes->length === 0) {
            throw new TestFileNameNotFoundException(sprintf('For FQCN: %s', $fullyQualifiedClassName));
        }

        return [
            'path' => $nodes[0]->getAttribute('file'),
            'time' => (float) $nodes[0]->getAttribute('time'),
        ];
    }
}