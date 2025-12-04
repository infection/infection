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

namespace Infection\Command\Git;

use function array_fill_keys;
use Infection\Git\Git;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use function trim;
use Webmozart\Assert\Assert;

final class GitBaseReferenceCommand extends Command
{
    private const BASE_OPTION = 'base';

    public function __construct(
        private readonly Git $git,
    ) {
        parent::__construct('git:base-reference');
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Gives the reference to the best common ancestors as possible with HEAD for a merge and falls back to the given base otherwise.',
            )
            ->addOption(
                self::BASE_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Git base, can be a commit hash, a short name or full name. Will lookup for the default base branch if none provided.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = self::createLogger($output);
        $base = self::getBase($input);

        if ($base === null) {
            $base = $this->git->getDefaultBase();

            $logger->notice(
                sprintf(
                    'No base found. Using the default base "%s".',
                    $base,
                ),
            );
        }

        $output->writeln(
            $this->git->getBaseReference($base),
        );

        return self::SUCCESS;
    }

    /**
     * @return non-empty-string|null
     */
    private static function getBase(InputInterface $input): ?string
    {
        $value = $input->getOption(self::BASE_OPTION);

        if ($value === null) {
            return null;
        }

        $trimmedValue = trim($value);

        Assert::stringNotEmpty(
            $trimmedValue,
            sprintf(
                'Expected a non-blank value for the option "--%s".',
                self::BASE_OPTION,
            ),
        );

        return $trimmedValue;
    }

    private static function createLogger(OutputInterface $output): LoggerInterface
    {
        return new ConsoleLogger(
            $output,
            // We use this logger purely for logging extra info to the user and
            // keep the STDOUT clean for allowing copy/paste.
            formatLevelMap: array_fill_keys(
                [
                    LogLevel::EMERGENCY,
                    LogLevel::ALERT,
                    LogLevel::CRITICAL,
                    LogLevel::ERROR,
                    LogLevel::WARNING,
                    LogLevel::NOTICE,
                    LogLevel::INFO,
                    LogLevel::DEBUG,
                ],
                ConsoleLogger::ERROR,
            ),
        );
    }
}
