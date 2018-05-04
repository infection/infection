<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutant;

use Infection\MutationInterface;

final class Mutant implements MutantInterface
{
    private $mutatedFilePath;

    /**
     * @var MutationInterface
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

    public function __construct(string $mutatedFilePath, MutationInterface $mutation, string $diff, bool $isCoveredByTest, array $coverageTests)
    {
        $this->mutatedFilePath = $mutatedFilePath;
        $this->mutation = $mutation;
        $this->diff = $diff;
        $this->isCoveredByTest = $isCoveredByTest;
        $this->coverageTests = $coverageTests;
    }

    public function getMutatedFilePath(): string
    {
        return $this->mutatedFilePath;
    }

    public function getMutation(): MutationInterface
    {
        return $this->mutation;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function isCoveredByTest(): bool
    {
        return $this->isCoveredByTest;
    }

    public function getCoverageTests(): array
    {
        return $this->coverageTests;
    }
}
