<?php

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\Mutant;
use Infection\Mutator\Regex\PregQuote;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\Tests\Mutator\MutatorName;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

trait CreateMetricsCalculator
{
    private function createCompleteMetricsCalculator(): MetricsCalculator
    {
        $calculator = new MetricsCalculator();

        $calculator->collect(
            $this->createMutantProcess(
                0,
                For_::class,
                MutantProcess::CODE_ESCAPED,
                'escaped#0'
            )
        );
        $calculator->collect(
            $this->createMutantProcess(
                1,
                PregQuote::class,
                MutantProcess::CODE_ESCAPED,
                'escaped#1'
            )
        );

        $calculator->collect(
            $this->createMutantProcess(
                0,
                For_::class,
                MutantProcess::CODE_TIMED_OUT,
                'timedOut#0'
            )
        );
        $calculator->collect(
            $this->createMutantProcess(
                1,
                PregQuote::class,
                MutantProcess::CODE_TIMED_OUT,
                'timedOut#1'
            )
        );

        $calculator->collect(
            $this->createMutantProcess(
                0,
                For_::class,
                MutantProcess::CODE_KILLED,
                'killed#0'
            )
        );
        $calculator->collect(
            $this->createMutantProcess(
                1,
                PregQuote::class,
                MutantProcess::CODE_KILLED,
                'killed#1'
            )
        );

        $calculator->collect(
            $this->createMutantProcess(
                0,
                For_::class,
                MutantProcess::CODE_ERROR,
                'error#0'
            )
        );
        $calculator->collect(
            $this->createMutantProcess(
                1,
                PregQuote::class,
                MutantProcess::CODE_ERROR,
                'error#1'
            )
        );

        $calculator->collect(
            $this->createMutantProcess(
                0,
                For_::class,
                MutantProcess::CODE_NOT_COVERED,
                'notCovered#0'
            )
        );
        $calculator->collect(
            $this->createMutantProcess(
                1,
                PregQuote::class,
                MutantProcess::CODE_NOT_COVERED,
                'notCovered#1'
            )
        );

        return $calculator;
    }

    private function createMutantProcess(
        int $i,
        string $mutatorClassName,
        int $resultCode,
        string $echoMutatedMessage
    ): MutantProcess {
        Assert::oneOf($resultCode, MutantProcess::RESULT_CODES);

        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn('bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"')
        ;
        $processMock
            ->method('isStarted')
            ->willReturn(true)
        ;

        $mutantMock = $this->createMock(Mutant::class);
        $mutantMock
            ->method('getDiff')
            ->willReturn(<<<DIFF
--- Original
+++ New
@@ @@

- echo 'original';
+ echo '$echoMutatedMessage';

DIFF
            )
        ;

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock
            ->method('getProcess')
            ->willReturn($processMock)
        ;
        $mutantProcessMock
            ->method('getMutant')
            ->willReturn($mutantMock)
        ;
        $mutantProcessMock
            ->method('getMutatorName')
            ->willReturn(MutatorName::getName($mutatorClassName))
        ;
        $mutantProcessMock
            ->method('getResultCode')
            ->willReturn($resultCode)
        ;
        $mutantProcessMock
            ->method('getOriginalStartingLine')
            ->willReturn(10 - $i)
        ;
        $mutantProcessMock
            ->method('getOriginalFilePath')
            ->willReturn('foo/bar')
        ;

        return $mutantProcessMock;
    }
}
