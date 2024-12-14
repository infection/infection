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

namespace Infection\TestFramework;

use function array_key_exists;
use function class_exists;
use Infection\Console\IO;
use function sprintf;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @internal
 */
final readonly class AdapterInstallationDecider
{
    private const ADAPTER_NAME_TO_CLASS_MAP = [
        TestFrameworkTypes::CODECEPTION => 'Infection\TestFramework\Codeception\CodeceptionAdapter',
        TestFrameworkTypes::PHPSPEC => 'Infection\TestFramework\PhpSpec\PhpSpecAdapter',
    ];

    public function __construct(private QuestionHelper $questionHelper)
    {
    }

    public function shouldBeInstalled(string $adapterName, IO $io): bool
    {
        if (!array_key_exists($adapterName, self::ADAPTER_NAME_TO_CLASS_MAP)
            || class_exists(self::ADAPTER_NAME_TO_CLASS_MAP[$adapterName])
        ) {
            return false;
        }

        $io->newLine();

        $question = new ConfirmationQuestion(
            sprintf(
                <<<TEXT
                    We noticed you are using a test framework supported by an external Infection plugin.
                    Would you like to install <comment>%s</comment>? [<comment>yes</comment>]:
                    TEXT
                ,
                AdapterInstaller::OFFICIAL_ADAPTERS_MAP[$adapterName],
            ),
            true,
        );

        return $this->questionHelper->ask(
            $io->getInput(),
            $io->getOutput(),
            $question,
        );
    }
}
