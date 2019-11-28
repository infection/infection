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

namespace Infection\Tests\Config\Exception;

use Infection\Config\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;

final class InvalidConfigExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $exception = new InvalidConfigException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function test_invalid_mutator_creates_exception(): void
    {
        $wrongMutator = 'NonExistent Mutator';

        $exception = InvalidConfigException::invalidMutator($wrongMutator);

        $this->assertInstanceOf(InvalidConfigException::class, $exception);

        $expected = sprintf(
            'The "%s" mutator/profile was not recognized.',
            $wrongMutator
        );

        $this->assertSame($expected, $exception->getMessage());
    }

    public function test_invalid_profile_creates_exception(): void
    {
        $configFile = '@hello';
        $errorMessage = 'Wrong Mutator';

        $exception = InvalidConfigException::invalidProfile(
            $configFile,
            $errorMessage
        );

        $this->assertInstanceOf(InvalidConfigException::class, $exception);

        $expected = sprintf(
            'The "%s" profile contains the "%s" mutator which was not recognized.',
            $configFile,
            $errorMessage
        );

        $this->assertSame($expected, $exception->getMessage());
    }
}
