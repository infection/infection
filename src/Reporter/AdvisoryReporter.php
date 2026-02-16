<?php

declare(strict_types=1);

namespace Infection\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class AdvisoryReporter implements Reporter
{
    public function __construct(
        private OutputInterface $output,
    ) {
    }

    public function report(): void
    {
        $this->output->writeln(['', 'Please note that some mutants will inevitably be harmless (i.e. false positives).']);
    }
}