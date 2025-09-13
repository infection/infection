<?php

declare(strict_types=1);

namespace Infection\TestFramework\NewCoverage\PHPUnitXml\Index;

use DOMElement;

/**
 * Represents pieces of information gotten from the `totals` node in the index
 * file of the PHPUnit XML coverage report for a source file.
 */
final readonly class LinesCoverageSummary
{
    /**
     * @param int<0, max>   $total
     * @param int<0, max>   $comments
     * @param int<0, max>   $code
     * @param int<0, max>   $executable
     * @param int<0, max>   $executed
     * @param float<0, max> $percent
     */
    public function __construct(
        public int $total,
        public int $comments,
        public int $code,
        public int $executable,
        public int $executed,
        public float $percent,
    ) {
    }

    public static function fromNode(DOMElement $node): self
    {
        return new self(
            (int) $node->getAttribute('total'),
            (int) $node->getAttribute('comments'),
            (int) $node->getAttribute('code'),
            (int) $node->getAttribute('executable'),
            (int) $node->getAttribute('executed'),
            (float) $node->getAttribute('percent'),
        );
    }
}
