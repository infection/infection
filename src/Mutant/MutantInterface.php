<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutant;

use Infection\MutationInterface;

/**
 * @see Mutant
 */
interface MutantInterface
{
    public function getMutatedFilePath(): string;

    public function getMutation(): MutationInterface;

    public function getDiff(): string;

    public function isCoveredByTest(): bool;

    public function getCoverageTests(): array;
}
