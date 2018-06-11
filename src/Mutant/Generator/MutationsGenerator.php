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
use Infection\Mutator\Util\Exception\MutatorException;
use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorsGenerator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
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
     * @var array
     */
    private $whitelistedMutatorNames;

    /**
     * @var int
     */
    private $whitelistedMutatorNamesCount;

    /**
     * @var Mutator[]
     */
    private $defaultMutators;

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
        array $defaultMutators,
        array $whitelistedMutatorNames,
        EventDispatcherInterface $eventDispatcher,
        Parser $parser
    ) {
        $this->srcDirs = $srcDirs;
        $this->codeCoverageData = $codeCoverageData;
        $this->excludeDirsOrFiles = $excludeDirsOrFiles;
        $this->defaultMutators = $defaultMutators;
        $this->whitelistedMutatorNames = $whitelistedMutatorNames;
        $this->whitelistedMutatorNamesCount = \count($whitelistedMutatorNames);
        $this->eventDispatcher = $eventDispatcher;
        $this->parser = $parser;
    }

    /**
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param string $filter
     *
     * @return Mutation[]
     */
    public function generate(bool $onlyCovered, string $filter = ''): array
    {
        $sourceFilesFinder = new SourceFilesFinder($this->srcDirs, $this->excludeDirsOrFiles);
        $files = $sourceFilesFinder->getSourceFiles($filter);
        $allFilesMutations = [[]];
        $mutators = $this->getMutators();

        $this->eventDispatcher->dispatch(new MutationGeneratingStarted($files->count()));

        foreach ($files as $file) {
            if (!$onlyCovered || ($onlyCovered && $this->hasTests($file))) {
                $allFilesMutations[] = $this->getMutationsFromFile($file, $onlyCovered, $mutators);
            }

            $this->eventDispatcher->dispatch(new MutableFileProcessed());
        }

        $this->eventDispatcher->dispatch(new MutationGeneratingFinished());

        return array_merge(...$allFilesMutations);
    }

    /**
     * @param SplFileInfo $file
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param array $mutators
     *
     * @return Mutation[]
     */
    private function getMutationsFromFile(SplFileInfo $file, bool $onlyCovered, array $mutators): array
    {
        try {
            /** @var Node[] $initialStatements */
            $initialStatements = $this->parser->parse($file->getContents());
        } catch (\Throwable $t) {
            throw ParserException::fromInvalidFile($file, $t);
        }

        $traverser = new NodeTraverser();

        $mutationsCollectorVisitor = new MutationsCollectorVisitor(
            $mutators,
            $file->getRealPath(),
            $initialStatements,
            $this->codeCoverageData,
            $onlyCovered
        );

        $traverser->addVisitor($mutationsCollectorVisitor);
        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());

        try {
            $traverser->traverse($initialStatements);
        } catch (MutatorException $e) {
            throw MutatorException::traverseErrorWithBetterMessage($file, $e);
        }

        return $mutationsCollectorVisitor->getMutations();
    }

    private function hasTests(SplFileInfo $file): bool
    {
        return $this->codeCoverageData->hasTests($file->getRealPath());
    }

    /**
     * @return array|Mutator[]
     */
    private function getMutators(): array
    {
        if ($this->whitelistedMutatorNamesCount > 0) {
            $mutatorSettings = [];

            foreach ($this->whitelistedMutatorNames as $mutatorName) {
                $mutatorSettings[$mutatorName] = true;
            }
            $generator = new MutatorsGenerator($mutatorSettings);

            return $generator->generate();
        }

        return $this->defaultMutators;
    }
}
