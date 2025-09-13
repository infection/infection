<?php

declare(strict_types=1);

namespace Infection\TestFramework\NewCoverage\PHPUnitXml\File;

use DOMElement;
use Webmozart\Assert\Assert;
use function array_map;
use function iterator_to_array;

/**
 * This represents the information available in the `file.coverage.line` element
 * of a source file of the PHPUnit XML coverage report.
 */
final readonly class LineCoverage
{
    /**
     * @param int<0, max> $lineNumber
     * @param non-empty-list<string> $coveredBy
     */
    public function __construct(
        public int $lineNumber,
        public array $coveredBy,
    ) {
    }

    public static function fromNode(
        DOMElement $node,
    ): self
    {
        Assert::same('line', $node->tagName);

        return new self(
            (int) $node->getAttribute('nr'),
            array_map(
                self::parseCoveredBy(...),
                iterator_to_array($node->getElementsByTagName('covered')),
            ),
        );
    }

    private static function parseCoveredBy(DOMElement $node): string
    {
        Assert::same('covered', $node->tagName);

        return $node->getAttribute('by');
    }
}
