<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Benchmark\Tracing;

use Infection\Benchmark\InstrumentorFactory;
use LogicException;
use const PHP_INT_MAX;
use function sprintf;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

require_once __DIR__ . '/../../../vendor/autoload.php';

const MAX_TRACE_COUNT_OPT = 'max-trace-count';
const PERCENTAGE_OPT = 'percentage';
const DEBUG_OPT = 'debug';

$input = new ArgvInput(
    null,
    new InputDefinition([
        new InputOption(
            MAX_TRACE_COUNT_OPT,
            null,
            InputOption::VALUE_REQUIRED,
            'Maximum number of traces retrieved. Use -1 for no maximum.',
            '-1',
        ),
        new InputOption(
            PERCENTAGE_OPT,
            null,
            InputOption::VALUE_REQUIRED,
            'Percentage of sources to process. [0,1], defaults to 1 = 100% of the sources processed.',
            1.,
        ),
        new InputOption(
            DEBUG_OPT,
            null,
            InputOption::VALUE_NONE,
            'To use to execute the code without actually profiling.',
        ),
    ]),
);
$output = new ConsoleOutput();
$io = new SymfonyStyle($input, $output);

/** @var positive-int $maxTraceCount */
$maxTraceCount = (static function (InputInterface $input, string $optionName): int {
    $option = $input->getOption($optionName);

    Assert::integerish(
        $option,
        sprintf(
            'Expected value of option "%s" to be integerish. Got "%s".',
            $optionName,
            $option,
        ),
    );

    $intValue = (int) $option;

    if ($intValue === -1) {
        return PHP_INT_MAX;
    }

    Assert::positiveInteger(
        $intValue,
        sprintf(
            'Expected value of option "%s" to be a positive integer or -1. Got "%s".',
            $optionName,
            $intValue,
        ),
    );

    return $intValue;
})($input, MAX_TRACE_COUNT_OPT);

/** @var float $percentage */
$percentage = (static function (InputInterface $input, string $optionName): float {
    $option = $input->getOption($optionName);

    Assert::numeric(
        $option,
        sprintf(
            'Expected value of option "%s" to be numeric. Got "%s".',
            $optionName,
            $option,
        ),
    );

    $floatValue = (float) $option;

    Assert::range(
        $floatValue,
        0.,
        1.,
        sprintf(
            'Expected value of option "%s" to be an element of [0,1]. Got "%s".',
            $optionName,
            $floatValue,
        ),
    );

    return $floatValue;
})($input, PERCENTAGE_OPT);

$debug = $input->getOption(DEBUG_OPT);

$instrumentor = InstrumentorFactory::create($debug);

$count = $instrumentor->profile(
    static fn () => (require __DIR__ . '/create-main.php')($maxTraceCount, $percentage),
    5,
    $io,
);

if ($count === 0) {
    throw new LogicException('Something went wrong, no traces were actually generated.');
}

$output->writeln(
    sprintf(
        '%d trace(s) generated.',
        $count,
    ),
);
