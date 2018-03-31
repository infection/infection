<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutant;

use Infection\Differ\Differ;
use Infection\MutationInterface;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\CloneVisitor;
use Infection\Visitor\MutatorVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;

final class MutantCreator
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

    /**
     * @var string[]
     */
    private $prettyPrintedCache = [];

    public function __construct(string $tempDir, Differ $differ, Standard $prettyPrinter)
    {
        $this->tempDir = $tempDir;
        $this->differ = $differ;
        $this->prettyPrinter = $prettyPrinter;
    }

    public function create(MutationInterface $mutation, CodeCoverageData $codeCoverageData): MutantInterface
    {
        $mutatedFilePath = sprintf('%s/mutant.%s.infection.php', $this->tempDir, $mutation->getHash());

        $mutatedCode = $this->createMutatedCode($mutation, $mutatedFilePath);

        $originalPrettyPrintedFile = $this->getOriginalPrettyPrintedFile($mutation->getOriginalFilePath(), $mutation->getOriginalFileAst());

        $diff = $this->differ->diff($originalPrettyPrintedFile, $mutatedCode);

        return new Mutant(
            $mutatedFilePath,
            $mutation,
            $diff,
            $mutation->isCoveredByTest(),
            $codeCoverageData->getAllTestsFor($mutation)
        );
    }

    private function createMutatedCode(MutationInterface $mutation, string $mutatedFilePath): string
    {
        if (file_exists($mutatedFilePath)) {
            return file_get_contents($mutatedFilePath);
        }
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new CloneVisitor());
        $traverser->addVisitor(new MutatorVisitor($mutation));

        $mutatedStatements = $traverser->traverse($mutation->getOriginalFileAst());

        $mutatedCode = $this->prettyPrinter->prettyPrintFile($mutatedStatements);
        file_put_contents($mutatedFilePath, $mutatedCode);

        return $mutatedCode;
    }

    private function getOriginalPrettyPrintedFile(string $originalFilePath, array $originalStatements): string
    {
        return $this->prettyPrintedCache[$originalFilePath]
            ?? $this->prettyPrintedCache[$originalFilePath] = $this->prettyPrinter->prettyPrintFile($originalStatements);
    }
}
