<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
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
    /**
     * @var array
     */
    private $coverageTests;

    public function __construct(string $mutatedFilePath, Mutation $mutation, string $diff, bool $isCoveredByTest, array $coverageTests)
    {
        $this->mutatedFilePath = $mutatedFilePath;
        $this->mutation = $mutation;
        $this->diff = $diff;
        $this->isCoveredByTest = $isCoveredByTest;
        $this->coverageTests = $coverageTests;
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

    public function getDiff(): string
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

    public function getCoverageTests(): array
    {
        return $this->coverageTests;
    }
}
