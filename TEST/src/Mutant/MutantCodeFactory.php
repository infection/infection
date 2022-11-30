<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutant;

use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\CloneVisitor;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\MutatorVisitor;
use _HumbugBox9658796bb9f0\PhpParser\NodeTraverser;
use _HumbugBox9658796bb9f0\PhpParser\PrettyPrinterAbstract;
class MutantCodeFactory
{
    public function __construct(private PrettyPrinterAbstract $printer)
    {
    }
    public function createCode(Mutation $mutation) : string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloneVisitor());
        $traverser->addVisitor(new MutatorVisitor($mutation));
        $mutatedStatements = $traverser->traverse($mutation->getOriginalFileAst());
        return $this->printer->prettyPrintFile($mutatedStatements);
    }
}
