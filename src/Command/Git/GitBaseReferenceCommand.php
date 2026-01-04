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

use Infection\Command\BaseCommand;
use Infection\Command\Git\Option\BaseOption;
use Infection\Console\IO;
use function sprintf;

/**
 * @internal
 */
final class GitBaseReferenceCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('git:base-reference');
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Gives the reference to the best common ancestors as possible with HEAD for a merge and falls back to the given base otherwise.',
        );

        BaseOption::addOption($this);
    }

    protected function executeCommand(IO $io): bool
    {
        $logger = LoggerFactory::create($io->getOutput());
        $base = BaseOption::get($io);

        $git = $this->getApplication()->getContainer()->getGit();

        if ($base === null) {
            $base = $git->getDefaultBase();

            $logger->notice(
                sprintf(
                    'No base found. Using the default base "%s".',
                    $base,
                ),
            );
        }

        $io->writeln(
            $git->getBaseReference($base),
        );

        return true;
    }
}
