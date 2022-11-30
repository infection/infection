<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutation;

use function array_intersect_key;
use function array_keys;
use function implode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\Mutator\ProfileList;
use _HumbugBox9658796bb9f0\Infection\PhpParser\MutatedNode;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit\JUnitTestCaseTimeAdder;
use function md5;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use function _HumbugBox9658796bb9f0\Safe\array_flip;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class Mutation
{
    private string $mutatorName;
    private array $attributes;
    private bool $coveredByTests;
    private ?float $nominalTimeToTest = null;
    private ?string $hash = null;
    public function __construct(private string $originalFilePath, private array $originalFileAst, string $mutatorName, array $attributes, private string $mutatedNodeClass, private MutatedNode $mutatedNode, private int $mutationByMutatorIndex, private array $tests)
    {
        Assert::oneOf($mutatorName, array_keys(ProfileList::ALL_MUTATORS));
        foreach (MutationAttributeKeys::ALL as $key) {
            Assert::keyExists($attributes, $key);
        }
        $this->mutatorName = $mutatorName;
        $this->attributes = array_intersect_key($attributes, array_flip(MutationAttributeKeys::ALL));
        $this->coveredByTests = $tests !== [];
    }
    public function getOriginalFilePath() : string
    {
        return $this->originalFilePath;
    }
    public function getOriginalFileAst() : array
    {
        return $this->originalFileAst;
    }
    public function getMutatorName() : string
    {
        return $this->mutatorName;
    }
    public function getAttributes() : array
    {
        return $this->attributes;
    }
    public function getOriginalStartingLine() : int
    {
        return (int) $this->attributes['startLine'];
    }
    public function getOriginalEndingLine() : int
    {
        return (int) $this->attributes['endLine'];
    }
    public function getOriginalStartFilePosition() : int
    {
        return (int) $this->attributes['startFilePos'];
    }
    public function getOriginalEndFilePosition() : int
    {
        return (int) $this->attributes['endFilePos'];
    }
    public function getMutatedNodeClass() : string
    {
        return $this->mutatedNodeClass;
    }
    public function getMutatedNode() : MutatedNode
    {
        return $this->mutatedNode;
    }
    public function isCoveredByTest() : bool
    {
        return $this->coveredByTests;
    }
    public function getAllTests() : array
    {
        return $this->tests;
    }
    public function getNominalTestExecutionTime() : float
    {
        return $this->nominalTimeToTest ?? ($this->nominalTimeToTest = (new JUnitTestCaseTimeAdder($this->tests))->getTotalTestTime());
    }
    public function getHash() : string
    {
        return $this->hash ?? ($this->hash = $this->createHash());
    }
    private function createHash() : string
    {
        $hashKeys = [$this->originalFilePath, $this->mutatorName, $this->mutationByMutatorIndex];
        foreach ($this->attributes as $attribute) {
            $hashKeys[] = $attribute;
        }
        return md5(implode('_', $hashKeys));
    }
}
