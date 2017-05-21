<?php

declare(strict_types=1);


namespace Infection\Mutant;


use Infection\Mutation;

class Mutant
{
    private $mutatedFilePath;

    /**
     * @var Mutation
     */
    private $mutation;

    /**
     * @var string
     */
    private $diff;

    /**
     * @var bool
     */
    private $isCoveredByTest;

    public function __construct(string $mutatedFilePath, Mutation $mutation, string $diff, bool $isCoveredByTest)
    {
        $this->mutatedFilePath = $mutatedFilePath;
        $this->mutation = $mutation;
        $this->diff = $diff;
        $this->isCoveredByTest = $isCoveredByTest;
    }

    /**
     * @return string
     */
    public function getMutatedFilePath(): string
    {
        return $this->mutatedFilePath;
    }

    /**
     * @return Mutation
     */
    public function getMutation(): Mutation
    {
        return $this->mutation;
    }

    public function getDiff() : string
    {
        return $this->diff;
    }

    /**
     * @return bool
     */
    public function isCoveredByTest(): bool
    {
        return $this->isCoveredByTest;
    }
}