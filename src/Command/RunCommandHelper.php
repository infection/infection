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

namespace Infection\Command;

use function getenv;
use function in_array;
use Infection\Container;
use Infection\Resource\Processor\CpuCoresCountProvider;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use InvalidArgumentException;
use function is_numeric;
use function max;
use function sprintf;
use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class RunCommandHelper
{
    public function __construct(
        private readonly InputInterface $input,
    ) {
    }

    public function getUseGitHubLogger(): ?bool
    {
        // on e2e environment, we don't need github logger
        if (getenv('INFECTION_E2E_TESTS_ENV') !== false) {
            return false;
        }

        $useGitHubLogger = $this->input->getOption(RunCommand::OPTION_LOGGER_GITHUB);

        // `false` means the option was not provided at all -> user does not care and it will be auto-detected
        // `null` means the option was provided without any argument -> user wants to enable it
        // any string: the argument provided, but only `'true'` and `'false` are supported
        if ($useGitHubLogger === false) {
            return null;
        }

        if ($useGitHubLogger === null) {
            return true;
        }

        if ($useGitHubLogger === 'true') {
            return true;
        }

        if ($useGitHubLogger === 'false') {
            return false;
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot pass "%s" to "--%s": only "true", "false" or no argument is supported',
            $useGitHubLogger,
            RunCommand::OPTION_LOGGER_GITHUB,
        ));
    }

    public function getThreadCount(): ?int
    {
        $threads = $this->input->getOption(RunCommand::OPTION_THREADS);

        // user didn't pass `--threads` option
        if ($threads === null) {
            return null;
        }

        // user passed `--threads=<int>` option
        if (is_numeric($threads)) {
            return (int) $threads;
        }

        // user passed `--threads=max` option
        Assert::same($threads, 'max', sprintf('The value of option `--threads` must be of type integer or string "max". String "%s" provided.', $threads));

        // we subtract 1 here to not use all the available cores by Infection
        return max(1, CpuCoresCountProvider::provide() - 1);
    }

    public function getMapSourceClassToTest(): ?string
    {
        $inputValue = $this->input->getOption(RunCommand::OPTION_MAP_SOURCE_CLASS_TO_TEST);

        // `false` means the option was not provided at all -> user does not care and it will be auto-detected
        // `null` means the option was provided without any argument -> user wants to enable it
        // any string: the argument provided, but only `'simple'` is allowed for now
        if ($inputValue === false) {
            return null;
        }

        if ($inputValue === null) {
            return MapSourceClassToTestStrategy::SIMPLE;
        }

        if (!in_array($inputValue, MapSourceClassToTestStrategy::getAll(), true)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot pass "%s" to "--%s": only "%s" or no argument is supported',
                $inputValue,
                RunCommand::OPTION_MAP_SOURCE_CLASS_TO_TEST,
                MapSourceClassToTestStrategy::SIMPLE,
            ));
        }

        return $inputValue;
    }

    public function getNumberOfShownMutations(): ?int
    {
        $shownMutations = $this->input->getOption(RunCommand::OPTION_SHOW_MUTATIONS);

        // user didn't pass `--show-mutations` option
        if ($shownMutations === null) {
            return Container::DEFAULT_SHOW_MUTATIONS;
        }

        // user passed `--show-mutations=<int>` option
        if (is_numeric($shownMutations)) {
            return (int) $shownMutations;
        }

        // user passed `--show-mutations=max` option
        Assert::same($shownMutations, 'max', sprintf('The value of option `--show-mutations` must be of type integer or string "max". String "%s" provided.', $shownMutations));

        return null; // unlimited mutations
    }
}
