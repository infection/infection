<?php

declare(strict_types=1);

namespace Infection\Tests\Console\OutputFormatter;

use Infection\Console\IO;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Tests\Fixtures\Console\FakeInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\BufferedOutput;
use function Infection\Tests\normalizeLineReturn;
use function strip_tags;

final class ProgressFormatterTest extends TestCase
{
    // Must be a prime number superior to 2
    private const TOTAL_MUTATIONS = 127;

    public function test_its_start_logs_the_initial_starting_text(): void
    {
        $output = new BufferedOutput();

        $formatter = new ProgressFormatter(new ProgressBar($output));

        $formatter->start(10);

        $this->assertSame(
            '  0/10 [>---------------------------]   0%',
            $output->fetch()
        );
    }

    public function test_it_prints_the_progress(): void
    {
        $output = new BufferedOutput();

        $formatter = new ProgressFormatter(
            new ProgressBar($output, 0, -1)
        );

        $formatter->start(10);

        for ($i = 0; $i < 2; ++$i) {
            $formatter->advance(
                $this->createMutantExecutionResultsOfType(DetectionStatus::KILLED)[0],
                10
            );
        }

        $this->assertSame(normalizeLineReturn(
            <<<'TXT'
  0/10 [>---------------------------]   0%
  1/10 [==>-------------------------]  10%
  2/10 [=====>----------------------]  20%
TXT
        ),
            strip_tags($output->fetch())
        );
    }

    public function test_it_completes_the_progress_bar_on_finish(): void
    {
        $output = new BufferedOutput();

        $formatter = new ProgressFormatter(
            new ProgressBar($output, 0, -1)
        );

        $formatter->start(10);

        for ($i = 0; $i < 2; ++$i) {
            $formatter->advance(
                $this->createMutantExecutionResultsOfType(DetectionStatus::KILLED)[0],
                10
            );
        }

        $formatter->finish();

        $this->assertSame(normalizeLineReturn(
            <<<'TXT'
  0/10 [>---------------------------]   0%
  1/10 [==>-------------------------]  10%
  2/10 [=====>----------------------]  20%
 10/10 [============================] 100%
TXT
        ),
            strip_tags($output->fetch())
        );
    }

    private function createMutantExecutionResultsOfType(
        string $detectionStatus,
        int $count = 1
    ): array {
        $executionResults = [];

        for ($i = 0; $i < $count; ++$i) {
            $executionResult = $this->createMock(MutantExecutionResult::class);
            $executionResult
                ->method('getDetectionStatus')
                ->willReturn($detectionStatus)
            ;
            $executionResults[] = $executionResult;
        }

        return $executionResults;
    }
}
