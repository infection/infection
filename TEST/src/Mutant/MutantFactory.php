<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutant;

use _HumbugBox9658796bb9f0\Infection\Differ\Differ;
use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\Later\Interfaces\Deferred;
use function _HumbugBox9658796bb9f0\Later\lazy;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\PrettyPrinterAbstract;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
class MutantFactory
{
    private array $printedFileCache = [];
    public function __construct(private string $tmpDir, private Differ $differ, private PrettyPrinterAbstract $printer, private MutantCodeFactory $mutantCodeFactory)
    {
    }
    public function create(Mutation $mutation) : Mutant
    {
        $mutantFilePath = sprintf('%s/mutant.%s.infection.php', $this->tmpDir, $mutation->getHash());
        $mutatedCode = lazy($this->createMutatedCode($mutation));
        $originalPrettyPrintedFile = lazy($this->getOriginalPrettyPrintedFile($mutation->getOriginalFilePath(), $mutation->getOriginalFileAst()));
        return new Mutant($mutantFilePath, $mutation, $mutatedCode, lazy($this->createMutantDiff($originalPrettyPrintedFile, $mutation, $mutatedCode)), $originalPrettyPrintedFile);
    }
    private function createMutatedCode(Mutation $mutation) : iterable
    {
        (yield $this->mutantCodeFactory->createCode($mutation));
    }
    private function createMutantDiff(Deferred $originalPrettyPrintedFile, Mutation $mutation, Deferred $mutantCode) : iterable
    {
        (yield $this->differ->diff($originalPrettyPrintedFile->get(), $mutantCode->get()));
    }
    private function getOriginalPrettyPrintedFile(string $originalFilePath, array $originalStatements) : iterable
    {
        (yield $this->printedFileCache[$originalFilePath] ?? ($this->printedFileCache[$originalFilePath] = $this->printer->prettyPrintFile($originalStatements)));
    }
}
