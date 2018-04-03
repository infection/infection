<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Events;

use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;

final class MutantProcessFinished
{
    /**
     * @var MutantProcess
     */
    private $mutantProcess;

    public function __construct(MutantProcessInterface $mutantProcess)
    {
        $this->mutantProcess = $mutantProcess;
    }

    /**
     * @return MutantProcess
     */
    public function getMutantProcess(): MutantProcessInterface
    {
        return $this->mutantProcess;
    }
}
