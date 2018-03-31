<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process;

use Infection\Mutant\MutantInterface;
use Symfony\Component\Process\Process;

interface MutantProcessInterface
{
    public function getProcess(): Process;

    public function getMutant(): MutantInterface;

    public function markTimeout();

    public function getResultCode(): int;
}
