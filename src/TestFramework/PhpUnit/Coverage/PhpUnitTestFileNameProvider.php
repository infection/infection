<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Coverage;

use Infection\TestFramework\Coverage\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\TestFileNameProvider;

class PhpUnitTestFileNameProvider implements TestFileNameProvider
{
    /**
     * @var string
     */
    private $jUnitFilePath;

    public function __construct(string $jUnitFilePath)
    {

        $this->jUnitFilePath = $jUnitFilePath;
    }

    public function getFileNameByClass(string $fullyQualifiedClassName): string
    {

        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($this->jUnitFilePath));
        $xPath = new \DOMXPath($dom);

        $nodes = $xPath->query(sprintf('//testsuite[@name="%s"]', $fullyQualifiedClassName));

        if ($nodes->length === 0) {
            throw new TestFileNameNotFoundException(sprintf('For FQCN: %s', $fullyQualifiedClassName));
        }

        return $nodes[0]->getAttribute('file');
    }
}