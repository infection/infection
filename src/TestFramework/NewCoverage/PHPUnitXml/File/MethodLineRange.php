<?php

declare(strict_types=1);

namespace Infection\TestFramework\NewCoverage\PHPUnitXml\File;

// TODO: similar to SourceMethodLineRange with the method name...
use DOMElement;
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Webmozart\Assert\Assert;
use function array_map;
use function iterator_to_array;

final readonly class MethodLineRange
{
    /**
     * @param string $methodName E.g. "__construct": no namespace or class/trait name included.
     * @param positive-int $startLine
     * @param positive-int $endLine
     */
    public function __construct(
        public string $methodName,
        public int $startLine,
        public int $endLine,
    ) {
    }

    public static function tryFromNode(
        DOMElement $node,
    ): ?self {
        Assert::same('method', $node->tagName);

        // TODO: in the original code we deal with an int... Could be a float casted to an int
        $isCovered = ((float) $node->getAttribute('coverage')) > 0;

        if (!$isCovered) {
            return null;
        }

        return new self(
            $node->getAttribute('name'),
            (int) $node->getAttribute('start'),
            (int) $node->getAttribute('end'),
        );
    }
}
