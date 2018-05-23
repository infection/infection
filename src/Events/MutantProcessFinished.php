<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Events;

use Infection\Process\MutantProcessInterface;

/**
 * @internal
 */
final class MutantProcessFinished
{
    /**
     * @var MutantProcessInterface
     */
    private $mutantProcess;

    public function __construct(MutantProcessInterface $mutantProcess)
    {
        $this->mutantProcess = $mutantProcess;
    }

    public function getMutantProcess(): MutantProcessInterface
    {
        return $this->mutantProcess;
    }
}
