<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor;

use Infection\Source\Matcher\SourceLineMatcher;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class ExcludeUnchangedLines extends NodeVisitorAbstract
{
    public function __construct(
        private readonly SourceLineMatcher $sourceLineMatcher,
        private readonly string $filePath,
    ) {
    }

    public function enterNode(Node $node): null
    {
        $eligibility = LabelNodesAsEligibleVisitor::getEligibility($node);

        if (false !== $eligibility) {
            $this->labelUntouchedNodeAsIneligible($node);
        }

        return null;
    }

    private function labelUntouchedNodeAsIneligible(Node $node): void
    {
        /** @psalm-suppress InvalidArgument */
        $touches = $this->sourceLineMatcher->touches(
            $this->filePath,
            $node->getStartLine(),
            $node->getEndLine()
        );

        if (!$touches) {
            LabelNodesAsEligibleVisitor::markAsIneligible($node);
        }
    }
}