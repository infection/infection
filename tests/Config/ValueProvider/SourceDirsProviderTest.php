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

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Config\ValueProvider\SourceDirsProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class SourceDirsProviderTest extends AbstractBaseProviderTest
{
    /**
     * @var SourceDirsProvider
     */
    private $provider;

    /**
     * @var MockObject|SourceDirGuesser
     */
    private $sourceDirGuesser;

    protected function setUp(): void
    {
        $this->sourceDirGuesser = $this->createMock(SourceDirGuesser::class);

        $this->provider = new SourceDirsProvider(
            $this->createMock(ConsoleHelper::class),
            $this->getQuestionHelper(),
            $this->sourceDirGuesser
        );
    }

    public function test_it_uses_guesser_and_default_value(): void
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            $this->markTestSkipped('Stty is not available');
        }

        $this->sourceDirGuesser
            ->method('guess')
            ->willReturn(['src']);

        $sourceDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['src'], $sourceDirs);
    }

    public function test_it_uses_guesser_and_non_default_guessed_value(): void
    {
        $this->sourceDirGuesser
            ->method('guess')
            ->willReturn(['src/Namespace']);

        $sourceDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['src/Namespace'], $sourceDirs);
    }

    public function test_it_uses_guesser_and_multiple_guessed_dirs(): void
    {
        $this->sourceDirGuesser
            ->method('guess')
            ->willReturn(['foo', 'bar']);

        $sourceDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['foo', 'bar'], $sourceDirs);
    }

    public function test_it_fills_choices_with_current_dir(): void
    {
        $this->sourceDirGuesser
            ->method('guess')
            ->willReturn(['src']);

        $sourceDirs = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("0\n")),
            $this->createOutputInterface(),
            ['src']
        );

        $this->assertSame(['.'], $sourceDirs);
    }

    public function test_it_throws_exception_when_current_dir_is_selected_with_another_dir(): void
    {
        $this->sourceDirGuesser
            ->method('guess')
            ->willReturn(['src']);

        $this->expectException(\LogicException::class);

        $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("0,1\n")),
            $this->createOutputInterface(),
            ['src']
        );
    }
}
