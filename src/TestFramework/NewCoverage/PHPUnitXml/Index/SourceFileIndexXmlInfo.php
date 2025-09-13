<?php

declare(strict_types=1);

namespace Infection\TestFramework\NewCoverage\PHPUnitXml\Index;

use DOMElement;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;
use function str_ends_with;
use function substr;

/**
 * Represents information about a source file from the index file of the PHPUnit
 * XML coverage report.
 *
 * TODO: to replace SourceFileInfoProvider
 */
final readonly class SourceFileIndexXmlInfo
{
    public function __construct(
        public string $sourcePathname,
        public string $coveragePathname,
        private LinesCoverageSummary $linesCoverageSummary,
    ) {
    }

    public function hasExecutedCode(): bool
    {
        return $this->linesCoverageSummary->executed > 0;
    }

    public static function fromNode(
        DOMElement $node,
        string $coverageDirPathname,
        string $coverageProjectSource,
    ): self
    {
        $coverageRelativePath = $node->getAttribute('href');
        $coveragePathname = Path::join($coverageDirPathname, $coverageRelativePath);
        Assert::true(str_ends_with($coveragePathname, '.php.xml'));

        $sourcePathname = Path::join(
            $coverageProjectSource,
            substr(
                $coverageRelativePath,
                0,
                -4,
            ),
        );

        $totals = $node->firstElementChild;
        Assert::string('totals', $totals->tagName);

        $lines = $totals->firstElementChild;
        Assert::string('lines', $totals->tagName);

        return new self(
            $sourcePathname,
            $coveragePathname,
            LinesCoverageSummary::fromNode($lines),
        );
    }
}
