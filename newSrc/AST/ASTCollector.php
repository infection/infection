<?php

declare(strict_types=1);

namespace newSrc\AST;

use Infection\PhpParser\FileParser;
use newSrc\AST\NodeVisitor\AddTypesVisitor;
use newSrc\AST\NodeVisitor\ExcludeIgnoredNodesVisitor;
use newSrc\AST\NodeVisitor\ExcludeUnchangedNodesVisitor;
use newSrc\AST\NodeVisitor\ExcludeUncoveredNodesVisitor;
use newSrc\AST\NodeVisitor\LabelAridCodeVisitor;
use newSrc\AST\NodeVisitor\LabelNodeAsEligibleVisitor;
use newSrc\Engine\Envelope;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use SplFileInfo;

// Parses the sources and provide a rich annotated AST.
final class ASTCollector
{
    /**
     * @param NodeVisitor[] $nodeVisitors
     */
    public function __construct(
        private FileParser $parser,
        private array $nodeVisitors,
    ) {
        // This should be injected
        $this->nodeVisitors = [
            new ExcludeUncoveredNodesVisitor(),
            new ExcludeUnchangedNodesVisitor(), // only if we do a diff execution
            new ExcludeIgnoredNodesVisitor(),
            new AddTypesVisitor(),
            new LabelAridCodeVisitor(),
            new LabelNodeAsEligibleVisitor(),
        ];
    }

    /**
     * @return iterable<Node[]>
     */
    public function collect(SplFileInfo $sourceFile): iterable
    {
        $statements = $this->parser->parse($sourceFile);

        $traverser = new NodeTraverser(...$this->nodeVisitors);
        $traversedStatements = $traverser->traverse($statements);

        yield $traversedStatements;
    }
}