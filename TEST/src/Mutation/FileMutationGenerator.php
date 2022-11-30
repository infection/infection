<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutation;

use _HumbugBox9658796bb9f0\Infection\Differ\FilesDiffChangedLines;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\NodeMutationGenerator;
use _HumbugBox9658796bb9f0\Infection\PhpParser\FileParser;
use _HumbugBox9658796bb9f0\Infection\PhpParser\NodeTraverserFactory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\UnparsableFile;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\MutationCollectorVisitor;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\LineRangeCalculator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class FileMutationGenerator
{
    public function __construct(private FileParser $parser, private NodeTraverserFactory $traverserFactory, private LineRangeCalculator $lineRangeCalculator, private FilesDiffChangedLines $filesDiffChangedLines, private bool $isForGitDiffLines, private ?string $gitDiffBase)
    {
    }
    public function generate(Trace $trace, bool $onlyCovered, array $mutators, array $nodeIgnorers) : iterable
    {
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::allIsInstanceOf($nodeIgnorers, NodeIgnorer::class);
        if ($onlyCovered && !$trace->hasTests()) {
            return;
        }
        $initialStatements = $this->parser->parse($trace->getSourceFileInfo());
        $mutationCollectorVisitor = new MutationCollectorVisitor(new NodeMutationGenerator($mutators, $trace->getRealPath(), $initialStatements, $trace, $onlyCovered, $this->isForGitDiffLines, $this->gitDiffBase, $this->lineRangeCalculator, $this->filesDiffChangedLines));
        $traverser = $this->traverserFactory->create($mutationCollectorVisitor, $nodeIgnorers);
        $traverser->traverse($initialStatements);
        yield from $mutationCollectorVisitor->getMutations();
    }
}
