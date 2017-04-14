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

    public function __construct(string $mutatedFilePath, Mutation $mutation)
    {
        $this->mutatedFilePath = $mutatedFilePath;
        $this->mutation = $mutation;
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
}