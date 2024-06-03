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

namespace Infection\Console;

use Infection\CannotBeInstantiated;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class OutputFormatterStyleConfigurator
{
    use CannotBeInstantiated;

    public static function configure(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        self::configureMutantStyle($formatter);
        self::configureDiffStyle($formatter);
        self::configureMutationScoreStyle($formatter);
    }

    private static function configureMutantStyle(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle('with-error', new OutputFormatterStyle('green'));
        $formatter->setStyle('with-syntax-error', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('uncovered', new OutputFormatterStyle('blue', null, ['bold']));
        $formatter->setStyle('timeout', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('escaped', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('killed', new OutputFormatterStyle('green'));
        $formatter->setStyle('skipped', new OutputFormatterStyle('magenta'));
        $formatter->setStyle('ignored', new OutputFormatterStyle('white'));
        $formatter->setStyle('code', new OutputFormatterStyle('white'));
    }

    private static function configureDiffStyle(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle('diff-add', new OutputFormatterStyle('green', null, ['bold']));
        $formatter->setStyle('diff-add-inline', new OutputFormatterStyle('green', null, ['bold', 'reverse']));
        $formatter->setStyle('diff-del', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('diff-del-inline', new OutputFormatterStyle('red', null, ['bold', 'reverse']));
    }

    private static function configureMutationScoreStyle(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle(
            'low',
            new OutputFormatterStyle('red', null, ['bold']),
        );
        $formatter->setStyle(
            'medium',
            new OutputFormatterStyle('yellow', null, ['bold']),
        );
        $formatter->setStyle(
            'high',
            new OutputFormatterStyle('green', null, ['bold']),
        );
    }
}
