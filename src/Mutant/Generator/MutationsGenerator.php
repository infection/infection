<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutant\Generator;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutableFileProcessed;
use Infection\Events\MutationGeneratingFinished;
use Infection\Events\MutationGeneratingStarted;
use Infection\Finder\SourceFilesFinder;
use Infection\Mutant\Exception\ParserException;
use Infection\Mutation;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\CodeCoverageClassIgnoreVisitor;
use Infection\Visitor\CodeCoverageMethodIgnoreVisitor;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final class MutationsGenerator
{
    /**
     * @var array source directories
     */
    private $srcDirs;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;

    /**
     * @var array
     */
    private $excludeDirsOrFiles;

    /**
     * @var Mutator[]
     */
    private $mutators;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(
        array $srcDirs,
        array $excludeDirsOrFiles,
        CodeCoverageData $codeCoverageData,
        array $mutators,
        EventDispatcherInterface $eventDispatcher,
        Parser $parser
    ) {
        $this->srcDirs = $srcDirs;
        $this->codeCoverageData = $codeCoverageData;
        $this->excludeDirsOrFiles = $excludeDirsOrFiles;
        $this->mutators = $mutators;
        $this->eventDispatcher = $eventDispatcher;
        $this->parser = $parser;
    }

    /**
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param string $filter
     * @param NodeVisitorAbstract[] $extraNodeVisitors
     *
     * @return Mutation[]
     */
    public function generate(bool $onlyCovered, string $filter = '', array $extraNodeVisitors = []): array
    {
        $sourceFilesFinder = new SourceFilesFinder($this->srcDirs, $this->excludeDirsOrFiles);
        $files = $sourceFilesFinder->getSourceFiles($filter);
        $allFilesMutations = [[]];

        $this->eventDispatcher->dispatch(new MutationGeneratingStarted($files->count()));

        foreach ($files as $file) {
            if (!$onlyCovered || $this->hasTests($file)) {
                $allFilesMutations[] = $this->getMutationsFromFile($file, $onlyCovered, $extraNodeVisitors);
            }

            $this->eventDispatcher->dispatch(new MutableFileProcessed());
        }

        $this->eventDispatcher->dispatch(new MutationGeneratingFinished());

        return array_merge(...$allFilesMutations);
    }

    /**
     * @param SplFileInfo $file
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param NodeVisitorAbstract[] $extraNodeVisitors extra visitors to influence to mutation collection process
     *
     * @return Mutation[]
     */
    private function getMutationsFromFile(SplFileInfo $file, bool $onlyCovered, array $extraNodeVisitors): array
    {
        try {
            /** @var Node[] $initialStatements */
            $initialStatements = $this->parser->parse($file->getContents());
        } catch (\Throwable $t) {
            throw ParserException::fromInvalidFile($file, $t);
        }

        $traverser = new NodeTraverser();
        $filePath = $file->getRealPath();
        \assert(\is_string($filePath));

        $mutationsCollectorVisitor = new MutationsCollectorVisitor(
            $this->mutators,
            $filePath,
            $initialStatements,
            $this->codeCoverageData,
            $onlyCovered
        );

        $orderedVisitors = [
            40 => new ParentConnectorVisitor(),
            30 => new FullyQualifiedClassNameVisitor(),
            20 => new ReflectionVisitor(),
            10 => $mutationsCollectorVisitor,
        ];

        $visitorsQueue = new \SplPriorityQueue();

        foreach ($orderedVisitors as $priority => $visitor) {
            $visitorsQueue->insert($visitor, $priority);
        }

        foreach ($extraNodeVisitors as $priority => $visitor) {
            $visitorsQueue->insert($visitor, $priority);
        }

        foreach ($visitorsQueue as $visitor) {
            $traverser->addVisitor($visitor);
        }

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }

    private function hasTests(SplFileInfo $file): bool
    {
        $filePath = $file->getRealPath();
        \assert(\is_string($filePath));

        return $this->codeCoverageData->hasTests($filePath);
    }
}
