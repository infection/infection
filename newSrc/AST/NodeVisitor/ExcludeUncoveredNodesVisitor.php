<?php

declare(strict_types=1);

namespace newSrc\AST\NodeVisitor;

use newSrc\AST\Annotation;
use newSrc\AST\NodeAnnotator;
use newSrc\Trace\Tracer;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

// All files being traversed are covered by tests, but not all the code of that file is covered by tests.
final class ExcludeUncoveredNodesVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private Tracer $tracer,
    ) {
    }

    public function enterNode(Node $node): int|null
    {
        // TODO: get the node symbol
        $symbol = $this->getSymbol($node);

        if ($symbol === null) {
            return null;
        }

        // Note that, for instance, a static Analyser may or may not cover a symbol. We could configure
        // that within the tracer if we want to take PHPStan as a full-fledged test framework.
        if (!$this->tracer->hasTests($symbol)) {
            NodeAnnotator::annotate($node, Annotation::NOT_COVERED_BY_TESTS);

            return self::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    private function getSymbol(Node $node): ?string {
        // TODO
        return 'Foo::bar()';
    }
}
