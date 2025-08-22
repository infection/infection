<?php

declare(strict_types=1);

namespace newSrc\AST\AridCodeDetector;

use PhpParser\Node;
use function iter\any;

final class CodeDetectorRegistry implements AridCodeDetector
{
    /**
     * @var list<AridCodeDetector> $detectors
     */
    private readonly array $detectors;

    public function __construct(
         AridCodeDetector ...$detectors,
    ) {
        $this->detectors = $detectors;
    }

    public function isArid(Node $node): bool
    {
        return any(
            static fn (AridCodeDetector $detector): bool => $detector->isArid($node),
            $this->detectors,
        );
    }
}
