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

use function array_key_exists;
use function array_keys;
use Infection\Console\IO;
use Infection\Mutator\Definition;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorResolver;
use Infection\Mutator\ProfileList;
use function sprintf;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class DescribeCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('describe')
            ->setDescription('Describes a mutator')
            ->addArgument('Mutator name', InputArgument::OPTIONAL);
    }

    protected function executeCommand(IO $io): bool
    {
        $mutator = $io->getInput()->getArgument('Mutator name');

        if ($mutator === null) {
            $question = new Question('What mutator do you wish to describe?');
            $question->setAutocompleterValues(array_keys(ProfileList::ALL_MUTATORS));
            $mutator = $io->askQuestion(
                $question,
            );
        }

        if (!array_key_exists($mutator, ProfileList::ALL_MUTATORS)
            && !MutatorResolver::isValidMutator($mutator)
        ) {
            $io->error(sprintf(
                '"The %s mutator does not exist"',
                $mutator,
            ));

            return false;
        }

        $mutatorClass = ProfileList::ALL_MUTATORS[$mutator] ?? $mutator;

        Assert::subclassOf($mutatorClass, Mutator::class);

        /** @var Definition $definition */
        $definition = $mutatorClass::getDefinition();

        if ($definition === null) {
            $io->error(sprintf(
                'Mutator "%s" does not have a definition',
                $mutator,
            ));

            return false;
        }

        $io->writeln('Mutator Category: ' . $definition->getCategory());
        $io->writeln(['', 'Description:']);
        $io->writeln($definition->getDescription());

        $diff = $definition->getDiff();

        $diffColorizer = $this->getApplication()->getContainer()->getDiffColorizer();
        $io->writeln(
            [
                '',
                'For example:',
                $diffColorizer->colorize($diff),
            ],
        );

        $remedy = $definition->getRemedies();

        if ($remedy !== null) {
            $io->writeln('');
            $io->writeln($remedy);
        }

        return true;
    }
}
