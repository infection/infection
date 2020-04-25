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

namespace Infection\Tests\Config\ValueProvider;

use function exec;
use PHPUnit\Framework\TestCase;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\rewind;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Webmozart\Assert\Assert;

abstract class BaseProviderTest extends TestCase
{
    protected static $stty;

    final protected function getQuestionHelper(): QuestionHelper
    {
        return new QuestionHelper();
    }

    /**
     * @return resource
     */
    final protected function getInputStream(string $input)
    {
        $stream = fopen('php://memory', 'rb+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }

    final protected function createStreamOutput(): StreamOutput
    {
        return new StreamOutput(fopen('php://memory', 'rb+', false));
    }

    /**
     * @param resource $stream
     */
    final protected function createStreamableInput(
        $stream,
        bool $interactive = true
    ): InputInterface {
        Assert::resource($stream);

        $input = new StringInput('');
        $input->setStream($stream);
        $input->setInteractive($interactive);

        return $input;
    }

    /**
     * @see \Symfony\Component\Console\Terminal::hasSttyAvailable()
     */
    final protected function hasSttyAvailable(): bool
    {
        if (self::$stty !== null) {
            return self::$stty;
        }

        exec('stty 2>&1', $output, $exitcode);

        return self::$stty = $exitcode === 0;
    }
}
