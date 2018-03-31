<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console\OutputFormatter;

use Infection\Process\MutantProcessInterface;
use Symfony\Component\Console\Helper\ProgressBar;

final class ProgressFormatter extends AbstractOutputFormatter
{
    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    public function start(int $mutationCount)
    {
        parent::start($mutationCount);

        $this->progressBar->start($mutationCount);
    }

    public function advance(MutantProcessInterface $mutantProcess, int $mutationCount)
    {
        parent::advance($mutantProcess, $mutationCount);

        $this->progressBar->advance();
    }

    public function finish()
    {
        parent::finish();

        $this->progressBar->finish();
    }
}
