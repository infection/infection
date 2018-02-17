<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutant;

use Infection\Differ\Differ;
use Infection\Mutation;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\CloneVisitor;
use Infection\Visitor\MutatorVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;

class MutantCreator
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct(string $tempDir, Differ $differ, Standard $prettyPrinter)
    {
        $this->tempDir = $tempDir;
        $this->differ = $differ;
        $this->prettyPrinter = $prettyPrinter;
    }

    private $prettyPrintedCache = [];

    public function create(Mutation $mutation, CodeCoverageData $codeCoverageData): Mutant
    {
        $originalStatements = $mutation->getOriginalFileAst();

        $mutatedFilePath = sprintf('%s/mutant.%s.infection.php', $this->tempDir, $mutation->getHash());

        if (file_exists($mutatedFilePath)) {
            $mutatedCode = file_get_contents($mutatedFilePath);
        } else {
            $traverser = new NodeTraverser();

            $traverser->addVisitor(new CloneVisitor());
            $traverser->addVisitor(new MutatorVisitor($mutation));

            $mutatedStatements = $traverser->traverse($originalStatements);

            $mutatedCode = $this->prettyPrinter->prettyPrintFile($mutatedStatements);

            file_put_contents($mutatedFilePath, $mutatedCode);
        }

        $originalFilePath = $mutation->getOriginalFilePath();

        $originalPrettyPrintedFile = $this->prettyPrintedCache[$originalFilePath]
            ?? $this->prettyPrintedCache[$originalFilePath] = $this->prettyPrinter->prettyPrintFile($originalStatements);

        $diff = $this->differ->diff($originalPrettyPrintedFile, $mutatedCode);

        $isCoveredByTest = $this->isCoveredByTest($mutation, $codeCoverageData);

        return new Mutant(
            $mutatedFilePath,
            $mutation,
            $diff,
            $isCoveredByTest,
            $codeCoverageData->getAllTestsFor($mutation)
        );
    }

    private function isCoveredByTest(Mutation $mutation, CodeCoverageData $codeCoverageData): bool
    {
        $line = $mutation->getAttributes()['startLine'];
        $filePath = $mutation->getOriginalFilePath();

        if ($mutation->isOnFunctionSignature()) {
            return $codeCoverageData->hasExecutedMethodOnLine($filePath, $line);
        }

        return $codeCoverageData->hasTestsOnLine($filePath, $line);
    }
}
