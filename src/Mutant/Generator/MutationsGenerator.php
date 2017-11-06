<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutant\Generator;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\MutableFileProcessed;
use Infection\Events\MutationGeneratingFinished;
use Infection\Events\MutationGeneratingStarted;
use Infection\Finder\SourceFilesFinder;
use Infection\Mutation;
use Infection\Mutator\Mutator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\ReflectionVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\WrappedFunctionInfoCollectorVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Symfony\Component\Finder\SplFileInfo;

class MutationsGenerator
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
     * @var EventDispatcher
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
        EventDispatcher $eventDispatcher,
        Parser $parser)
    {
        $this->srcDirs = $srcDirs;
        $this->codeCoverageData = $codeCoverageData;
        $this->excludeDirsOrFiles = $excludeDirsOrFiles;
        $this->defaultMutators = $defaultMutators;
        $this->whitelistedMutatorNames = array_map('strtolower', $whitelistedMutatorNames);
        $this->whitelistedMutatorNamesCount = count($whitelistedMutatorNames);
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

        $this->eventDispatcher->dispatch(new MutationGeneratingStarted($files->count()));

        foreach ($files as $file) {
            if (!$onlyCovered || ($onlyCovered && $this->hasTests($file))) {
                $allFilesMutations[] = $this->getMutationsFromFile($file, $onlyCovered);
            }

            $this->eventDispatcher->dispatch(new MutableFileProcessed());
        }

        $this->eventDispatcher->dispatch(new MutationGeneratingFinished());

        return array_merge(...$allFilesMutations);
    }

    /**
     * @param SplFileInfo $file
     * @param bool $onlyCovered mutate only covered by tests lines of code
     *
     * @return Mutation[]
     */
    private function getMutationsFromFile(SplFileInfo $file, bool $onlyCovered): array
    {
        $initialStatements = $this->parser->parse($file->getContents());

        $traverser = new NodeTraverser();
        $mutators = $this->getMutators();

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

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }

    private function hasTests(SplFileInfo $file): bool
    {
        return $this->codeCoverageData->hasTests($file->getRealPath());
    }

    private function getMutators(): array
    {
        if ($this->whitelistedMutatorNamesCount > 0) {
            return array_filter(
                $this->defaultMutators,
                function (Mutator $mutator): bool {
                    return in_array(strtolower($mutator->getName()), $this->whitelistedMutatorNames, true);
                }
            );
        }

        return $this->defaultMutators;
    }
}
