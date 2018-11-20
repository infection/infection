<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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
use Infection\Config\InfectionConfig;
use Infection\Config\ValueProvider\TimeoutProvider;

/**
 * @internal
 */
final class TimeoutProviderTest extends AbstractBaseProviderTest
{
    /**
     * @var TimeoutProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new TimeoutProvider(
            $this->createMock(ConsoleHelper::class),
            $this->getQuestionHelper()
        );
    }

    public function test_it_uses_default_value(): void
    {
        $timeout = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface()
        );

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $timeout);
    }

    public function test_it_casts_any_value_to_integer(): void
    {
        $timeout = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("13\n")),
            $this->createOutputInterface()
        );

        $this->assertSame(13, $timeout);
    }

    /**
     * @dataProvider validatorProvider
     */
    public function test_it_does_not_allow_invalid_values($inputValue): void
    {
        $this->expectException(\RuntimeException::class);

        $timeout = $this->provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("{$inputValue}\n")),
            $this->createOutputInterface()
        );

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $timeout);
    }

    public function validatorProvider()
    {
        return [
            ['str'],
            [0],
            [-1],
            [0.1],
        ];
    }
}
