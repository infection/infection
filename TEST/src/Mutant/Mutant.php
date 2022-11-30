<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutant;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\Later\Interfaces\Deferred;
class Mutant
{
    public function __construct(private string $mutantFilePath, private Mutation $mutation, private Deferred $mutatedCode, private Deferred $diff, private Deferred $prettyPrintedOriginalCode)
    {
    }
    public function getFilePath() : string
    {
        return $this->mutantFilePath;
    }
    public function getMutation() : Mutation
    {
        return $this->mutation;
    }
    public function getMutatedCode() : Deferred
    {
        return $this->mutatedCode;
    }
    public function getPrettyPrintedOriginalCode() : Deferred
    {
        return $this->prettyPrintedOriginalCode;
    }
    public function getDiff() : Deferred
    {
        return $this->diff;
    }
    public function isCoveredByTest() : bool
    {
        return $this->mutation->isCoveredByTest();
    }
    public function getTests() : array
    {
        return $this->mutation->getAllTests();
    }
}
